<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import {
  getAuction,
  updateAuction,
  deleteAuction,
  addAuctionImages,
  deleteAuctionImage,
} from "../../api/auctions";

const router = useRouter();
const route = useRoute();

// ─── Helpers ──────────────────────────────────────────────────────
function formatRupiah(v) {
  if (!v) return "";
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(v);
}
function formatRupiahShort(v) {
  if (v >= 1000000) return v / 1000000 + "jt";
  if (v >= 1000) return v / 1000 + "rb";
  return v;
}
function formatDateTime(date, time) {
  if (!date) return "—";
  const d = new Date(`${date}T${time || "00:00"}`);
  return (
    d.toLocaleDateString("id-ID", {
      day: "numeric",
      month: "long",
      year: "numeric",
    }) +
    " pukul " +
    (time || "00:00")
  );
}

// ─── Static options ───────────────────────────────────────────────
const categories = [
  "Lukisan",
  "Patung",
  "Topeng Tradisional",
  "Ukiran Kayu",
  "Kerajinan Perak",
  "Barang Antik",
  "Seni Kontemporer",
  "Koleksi Langka",
  "Lainnya",
];
const conditions = ["Sangat Baik", "Baik", "Cukup Baik", "Perlu Restorasi"];
const minIncrementOptions = [50000, 100000, 250000, 500000, 1000000];
const todayStr = new Date().toISOString().split("T")[0];

// ─── Auction status ───────────────────────────────────────────────
const auctionStatus = ref("upcoming");
const canEdit = computed(() => auctionStatus.value === "upcoming" || auctionStatus.value === "scheduled");

// ─── Fetch & populate form ─────────────────────────────────────────
const isLoading = ref(true);

async function loadAuctionData() {
  isLoading.value = true;
  try {
    const res = await getAuction(route.params.id);
    const a = res.data.auction;

    form.value.name = a.name;
    form.value.category = a.category;
    form.value.condition = a.condition;
    form.value.artist = a.artist ?? "";
    form.value.year = a.year ?? "";
    form.value.description = a.description;
    form.value.startPrice = a.startPrice;
    form.value.minIncrement = a.minIncrement;
    form.value.buyNowEnabled = !!a.buyNowPrice;
    form.value.buyNowPrice = a.buyNowPrice ?? "";

    if (a.startsAt) {
      const startsAtDate = new Date(a.startsAt);
      form.value.startDate = startsAtDate.toISOString().split("T")[0];
      form.value.startTime = startsAtDate.toTimeString().split(" ")[0].substring(0, 5);
    }
    if (a.endsAt) {
      const endsAtDate = new Date(a.endsAt);
      form.value.endDate = endsAtDate.toISOString().split("T")[0];
      form.value.endTime = endsAtDate.toTimeString().split(" ")[0].substring(0, 5);
    }

    auctionStatus.value = a.status; // 'upcoming' | 'live' | 'ended'
    
    existingPhotos.value = (a.images ?? []).map(img => ({
      id: img.id,
      url: img.url,
      sortOrder: img.sortOrder,
      isMain: img.sortOrder === 0,
    }));
  } catch (err) {
    console.error("Gagal memuat data lelang:", err);
    alert("Gagal memuat data lelang.");
    router.push("/my-auctions");
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  loadAuctionData();
});

// ─── Form state ───────────────────────────────────────────────────
const form = ref({
  name: "",
  category: "",
  condition: "",
  artist: "",
  year: "",
  description: "",
  startPrice: "",
  minIncrement: 100000,
  buyNowEnabled: false,
  buyNowPrice: "",
  startDate: "",
  startTime: "09:00",
  endDate: "",
  endTime: "21:00",
});

const errors = ref({});
const isSubmitting = ref(false);

// ─── Existing photos (dari API) ───────────────────────────────────
const existingPhotos = ref([]);
const photosToDelete = ref([]); // ID foto yang akan dihapus saat save
const fullscreenPhoto = ref(null);

function markPhotoForDeletion(photo) {
  // Kalau foto utama (sort_order=0) dihapus, foto berikutnya jadi utama
  photosToDelete.value.push(photo.id);
  existingPhotos.value = existingPhotos.value.filter((p) => p.id !== photo.id);

  // Re-assign main
  if (photo.isMain && existingPhotos.value.length > 0) {
    existingPhotos.value[0].isMain = true;
    existingPhotos.value[0].sortOrder = 0;
  }
}

function movePhoto(idx, direction) {
  const arr = [...existingPhotos.value];
  const targetIdx = idx + direction;
  if (targetIdx < 0 || targetIdx >= arr.length) return;
  [arr[idx], arr[targetIdx]] = [arr[targetIdx], arr[idx]];
  arr.forEach((p, i) => {
    p.sortOrder = i;
    p.isMain = i === 0;
  });
  existingPhotos.value = arr;
}

// ─── New photos (baru ditambahkan) ───────────────────────────────
const newPhotoPreviews = ref([]);
const newPhotoFiles = ref([]);
const newPhotoDragOver = ref(false);

const totalPhotoCount = computed(
  () => existingPhotos.value.length + newPhotoPreviews.value.length,
);

function handleNewPhotoDrop(e) {
  newPhotoDragOver.value = false;
  const files = Array.from(e.dataTransfer.files);
  addNewPhotoFiles(files);
}

function handleNewPhotoInput(e) {
  const files = Array.from(e.target.files);
  addNewPhotoFiles(files);
}

function addNewPhotoFiles(files) {
  const remaining = 10 - totalPhotoCount.value;
  files.slice(0, remaining).forEach((f) => {
    if (!f.type.startsWith("image/")) return;
    newPhotoPreviews.value.push(URL.createObjectURL(f));
    newPhotoFiles.value.push(f);
  });
}

function removeNewPhoto(idx) {
  newPhotoPreviews.value.splice(idx, 1);
  newPhotoFiles.value.splice(idx, 1);
}

// ─── Duration ─────────────────────────────────────────────────────
const auctionDuration = computed(() => {
  if (!form.value.startDate || !form.value.endDate) return "";
  const start = new Date(`${form.value.startDate}T${form.value.startTime}`);
  const end = new Date(`${form.value.endDate}T${form.value.endTime}`);
  const diff = end - start;
  if (diff <= 0) return "";
  const days = Math.floor(diff / 86400000);
  const hours = Math.floor((diff % 86400000) / 3600000);
  const parts = [];
  if (days) parts.push(`${days} Hari`);
  if (hours) parts.push(`${hours} Jam`);
  return parts.join(" ") || "< 1 Jam";
});

// ─── Validation ───────────────────────────────────────────────────
function validate() {
  const e = {};
  if (!form.value.name.trim()) e.name = "Nama barang wajib diisi.";
  if (!form.value.category) e.category = "Pilih kategori barang.";
  if (!form.value.condition) e.condition = "Pilih kondisi barang.";
  if ((form.value.description || "").length < 30)
    e.description = "Deskripsi minimal 30 karakter.";
  if (totalPhotoCount.value === 0) e.photos = "Minimal satu foto wajib ada.";
  if (!form.value.startPrice || Number(form.value.startPrice) <= 0)
    e.startPrice = "Harga awal harus lebih besar dari nol.";
  if (!form.value.minIncrement || Number(form.value.minIncrement) <= 0)
    e.minIncrement = "Kelipatan bid harus lebih besar dari nol.";
  if (!form.value.startDate) e.startDate = "Waktu mulai wajib diisi.";
  if (!form.value.endDate) e.endDate = "Waktu berakhir wajib diisi.";
  if (form.value.startDate && form.value.endDate) {
    const s = new Date(`${form.value.startDate}T${form.value.startTime}`);
    const en = new Date(`${form.value.endDate}T${form.value.endTime}`);
    if (en <= s) e.endDate = "Waktu berakhir harus setelah waktu mulai.";
  }
  if (
    form.value.buyNowEnabled &&
    form.value.buyNowPrice &&
    Number(form.value.buyNowPrice) <= Number(form.value.startPrice)
  ) {
    e.buyNowPrice = "Harga Buy Now harus lebih besar dari harga awal.";
  }
  errors.value = e;
  return Object.keys(e).length === 0;
}

// ─── Modals ───────────────────────────────────────────────────────
const showSaveModal = ref(false);
const showDeleteModal = ref(false);

function openSaveModal() {
  if (!validate()) {
    setTimeout(() => {
      const el = document.querySelector(".field-error-msg");
      el?.scrollIntoView({ behavior: "smooth", block: "center" });
    }, 50);
    return;
  }
  showSaveModal.value = true;
}

async function confirmSave() {
  showSaveModal.value = false;
  isSubmitting.value = true;

  const auctionId = route.params.id;

  try {
    // 1. Hapus foto yang ditandai
    for (const photoId of photosToDelete.value) {
      await deleteAuctionImage(auctionId, photoId);
    }

    // 2. Upload foto baru jika ada
    if (newPhotoFiles.value.length > 0) {
      const formData = new FormData();
      newPhotoFiles.value.forEach((file) => {
        formData.append("photos[]", file);
      });
      await addAuctionImages(auctionId, formData);
    }

    // 3. Update data lelang, including the photo order
    const photoOrder = existingPhotos.value.map((p) => p.id);

    await updateAuction(auctionId, {
      title: form.value.name,
      category: form.value.category,
      condition: form.value.condition,
      artist: form.value.artist,
      year: form.value.year,
      description: form.value.description,
      starting_price: form.value.startPrice,
      bid_increment: form.value.minIncrement,
      buy_now_price: form.value.buyNowEnabled ? form.value.buyNowPrice : null,
      start_date: form.value.startDate,
      start_time: form.value.startTime,
      end_date: form.value.endDate,
      end_time: form.value.endTime,
      photo_order: photoOrder,
    });

    router.push("/my-auctions");
  } catch (err) {
    console.error("Gagal menyimpan perubahan lelang:", err);
    alert(err.response?.data?.message || "Gagal menyimpan perubahan.");
  } finally {
    isSubmitting.value = false;
  }
}

function openDeleteModal() {
  showDeleteModal.value = true;
}

async function confirmDelete() {
  showDeleteModal.value = false;
  isSubmitting.value = true;

  try {
    await deleteAuction(route.params.id);
    router.push("/my-auctions");
  } catch (err) {
    console.error("Gagal menghapus lelang:", err);
    alert(err.response?.data?.message || "Gagal menghapus lelang.");
  } finally {
    isSubmitting.value = false;
  }
}

function inputClass(error) {
  return [
    "w-full bg-white border rounded-lg px-4 py-3 text-sm outline-none transition-colors",
    error
      ? "border-red-300 focus:border-red-400"
      : "border-gray-200 focus:border-black",
  ].join(" ");
}

// Reset errors saat user mulai mengetik
watch(
  () => form.value.name,
  () => {
    errors.value.name = "";
  },
);
</script>

<template>
  <div class="bg-gray-50 min-h-screen font-sans">
    <!-- ── SAVE MODAL ────────────────────────────────────────────── -->
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
    >
      <div
        v-if="showSaveModal"
        class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-6"
        @click.self="showSaveModal = false"
      >
        <div class="bg-white rounded-2xl p-8 max-w-sm w-full shadow-2xl">
          <div
            class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mb-5"
          >
            <svg
              class="w-6 h-6 text-gray-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"
              />
            </svg>
          </div>
          <h3 class="text-lg font-bold mb-2">Simpan Perubahan</h3>
          <p class="text-gray-500 text-sm leading-relaxed mb-6">
            Apakah Anda yakin ingin menyimpan perubahan pada lelang ini?
          </p>
          <div class="flex gap-3">
            <button
              @click="showSaveModal = false"
              class="flex-1 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:border-black hover:text-black transition-colors"
            >
              Batal
            </button>
            <button
              @click="confirmSave"
              :disabled="isSubmitting"
              class="flex-1 py-2.5 bg-black text-white rounded-lg text-sm font-semibold hover:bg-gray-800 transition-colors disabled:opacity-60 flex items-center justify-center gap-2"
            >
              <svg
                v-if="isSubmitting"
                class="w-4 h-4 animate-spin"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                />
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                />
              </svg>
              {{ isSubmitting ? "Menyimpan..." : "Simpan" }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ── DELETE MODAL ──────────────────────────────────────────── -->
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
    >
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-6"
        @click.self="showDeleteModal = false"
      >
        <div class="bg-white rounded-2xl p-8 max-w-sm w-full shadow-2xl">
          <div
            class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center mb-5"
          >
            <svg
              class="w-6 h-6 text-red-500"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
              />
            </svg>
          </div>
          <h3 class="text-lg font-bold mb-2">Hapus Lelang</h3>
          <p class="text-gray-500 text-sm leading-relaxed mb-6">
            Tindakan ini akan menghapus data lelang secara permanen dan
            <span class="font-semibold text-gray-700"
              >tidak dapat dibatalkan</span
            >.
          </p>
          <div class="flex gap-3">
            <button
              @click="showDeleteModal = false"
              class="flex-1 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:border-black hover:text-black transition-colors"
            >
              Batal
            </button>
            <button
              @click="confirmDelete"
              class="flex-1 py-2.5 bg-red-500 text-white rounded-lg text-sm font-semibold hover:bg-red-600 transition-colors"
            >
              Hapus Permanen
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ── FULLSCREEN PHOTO PREVIEW ───────────────────────────────── -->
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
    >
      <div
        v-if="fullscreenPhoto"
        class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-6"
        @click="fullscreenPhoto = null"
      >
        <img
          :src="fullscreenPhoto"
          class="max-w-full max-h-full rounded-xl object-contain"
        />
        <button
          @click="fullscreenPhoto = null"
          class="absolute top-5 right-5 w-10 h-10 bg-white/10 backdrop-blur rounded-xl flex items-center justify-center text-white hover:bg-white/20 transition-colors"
        >
          <svg
            class="w-5 h-5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>
    </transition>

    <!-- ── LOADING STATE ──────────────────────────────────────────── -->
    <div v-if="isLoading" class="px-6 md:px-10 py-10">
      <div class="h-5 w-72 bg-gray-200 rounded-full animate-pulse mb-8"></div>
      <div class="h-8 w-48 bg-gray-200 rounded-lg animate-pulse mb-3"></div>
      <div class="flex gap-8">
        <div class="flex-1 space-y-4">
          <div class="h-48 bg-gray-200 rounded-2xl animate-pulse"></div>
          <div class="h-64 bg-gray-200 rounded-2xl animate-pulse"></div>
          <div class="h-48 bg-gray-200 rounded-2xl animate-pulse"></div>
        </div>
        <div class="w-72 hidden xl:block space-y-4">
          <div class="h-64 bg-gray-200 rounded-2xl animate-pulse"></div>
          <div class="h-48 bg-gray-200 rounded-2xl animate-pulse"></div>
        </div>
      </div>
    </div>

    <!-- ── CANNOT EDIT STATE (live/ended) ─────────────────────────── -->
    <div
      v-else-if="!canEdit"
      class="flex flex-col items-center justify-center py-32 px-6 text-center"
    >
      <div
        class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mb-5"
      >
        <svg
          class="w-8 h-8 text-gray-300"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
          />
        </svg>
      </div>
      <h3 class="font-semibold text-base mb-2">Lelang Tidak Dapat Diubah</h3>
      <p class="text-gray-400 text-sm max-w-xs leading-relaxed mb-6">
        Lelang tidak dapat diubah karena telah dimulai atau telah berakhir.
      </p>
      <button
        @click="router.push('/my-auctions')"
        class="px-6 py-2.5 bg-black text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors"
      >
        Kembali ke Karya Saya
      </button>
    </div>

    <!-- ── MAIN EDIT FORM ─────────────────────────────────────────── -->
    <div v-else class="px-6 md:px-10 py-8 mx-auto">
      <!-- Breadcrumb -->
      <nav class="flex items-center gap-2 text-sm text-gray-400 mb-8 flex-wrap">
        <router-link to="/" class="hover:text-black transition-colors"
          >Beranda</router-link
        >
        <svg
          class="w-3.5 h-3.5 shrink-0"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 5l7 7-7 7"
          />
        </svg>
        <router-link
          to="/my-auctions"
          class="hover:text-black transition-colors"
          >Karya Saya</router-link
        >
        <svg
          class="w-3.5 h-3.5 shrink-0"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 5l7 7-7 7"
          />
        </svg>
        <router-link
          :to="`/auction/${route.params.id}`"
          class="hover:text-black transition-colors truncate max-w-[160px]"
          >Detail Lelang</router-link
        >
        <svg
          class="w-3.5 h-3.5 shrink-0"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 5l7 7-7 7"
          />
        </svg>
        <span class="text-gray-700 font-medium">Edit Lelang</span>
      </nav>

      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
          <span
            class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400"
            >Formulir</span
          >
          <span
            class="text-xs font-medium bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full border border-amber-200"
          >
            🕐 Menunggu Dimulai
          </span>
        </div>
        <h1 class="text-3xl md:text-4xl font-bold mt-1">Edit Lelang</h1>
        <p class="text-gray-500 mt-3 text-sm max-w-xl leading-relaxed">
          Perbarui informasi lelang sebelum lelang dimulai dan dipublikasikan
          kepada peserta.
        </p>
      </div>

      <!-- Info banner -->
      <div
        class="flex items-start gap-3 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3.5 mb-8"
      >
        <svg
          class="w-4 h-4 text-blue-400 shrink-0 mt-0.5"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <p class="text-xs text-blue-700 leading-relaxed">
          Lelang dapat diedit karena belum dimulai dan belum menerima penawaran.
          Perubahan akan langsung berlaku setelah disimpan.
        </p>
      </div>

      <!-- ── MAIN LAYOUT ─────────────────────────────────────────── -->
      <div class="flex gap-8 items-start">
        <!-- FORM ───────────────────────────────────────────────────── -->
        <div class="flex-1 min-w-0 flex flex-col gap-6">
          <!-- ── SECTION 1: Informasi Barang ──────────────────────── -->
          <div
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
          >
            <div
              class="px-6 py-5 border-b border-gray-100 flex items-center gap-3"
            >
              <div
                class="w-8 h-8 rounded-lg bg-black flex items-center justify-center shrink-0"
              >
                <svg
                  class="w-4 h-4 text-white"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                  />
                </svg>
              </div>
              <div>
                <p class="font-semibold text-sm">Informasi Barang</p>
                <p class="text-xs text-gray-400 mt-0.5">
                  Nama, kategori, dan deskripsi
                </p>
              </div>
            </div>

            <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-5">
              <!-- Nama -->
              <div class="md:col-span-2">
                <label class="text-xs text-gray-500 mb-1.5 block font-medium">
                  Nama Barang Seni <span class="text-red-400">*</span>
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  placeholder="Contoh: Lukisan Bali Klasik Tahun 1980"
                  :class="inputClass(errors.name)"
                />
                <p
                  v-if="errors.name"
                  class="field-error-msg text-red-500 text-xs mt-1"
                >
                  {{ errors.name }}
                </p>
              </div>

              <!-- Kategori -->
              <div>
                <label class="text-xs text-gray-500 mb-1.5 block font-medium">
                  Kategori <span class="text-red-400">*</span>
                </label>
                <select
                  v-model="form.category"
                  :class="inputClass(errors.category)"
                >
                  <option value="">Pilih Kategori</option>
                  <option v-for="cat in categories" :key="cat" :value="cat">
                    {{ cat }}
                  </option>
                </select>
                <p
                  v-if="errors.category"
                  class="field-error-msg text-red-500 text-xs mt-1"
                >
                  {{ errors.category }}
                </p>
              </div>

              <!-- Kondisi -->
              <div>
                <label class="text-xs text-gray-500 mb-1.5 block font-medium">
                  Kondisi Barang <span class="text-red-400">*</span>
                </label>
                <select
                  v-model="form.condition"
                  :class="inputClass(errors.condition)"
                >
                  <option value="">Pilih Kondisi</option>
                  <option v-for="c in conditions" :key="c" :value="c">
                    {{ c }}
                  </option>
                </select>
                <p
                  v-if="errors.condition"
                  class="field-error-msg text-red-500 text-xs mt-1"
                >
                  {{ errors.condition }}
                </p>
              </div>

              <!-- Seniman -->
              <div>
                <label class="text-xs text-gray-500 mb-1.5 block font-medium">
                  Seniman / Pembuat
                  <span class="text-gray-300 font-normal">(Opsional)</span>
                </label>
                <input
                  v-model="form.artist"
                  type="text"
                  placeholder="Contoh: I Wayan Sudarsana"
                  class="w-full bg-white border border-gray-200 rounded-lg px-4 py-3 text-sm outline-none focus:border-black transition-colors"
                />
              </div>

              <!-- Tahun -->
              <div>
                <label class="text-xs text-gray-500 mb-1.5 block font-medium">
                  Tahun Pembuatan
                  <span class="text-gray-300 font-normal">(Opsional)</span>
                </label>
                <input
                  v-model="form.year"
                  type="number"
                  placeholder="Contoh: 1980"
                  min="1000"
                  max="2099"
                  class="w-full bg-white border border-gray-200 rounded-lg px-4 py-3 text-sm outline-none focus:border-black transition-colors"
                />
              </div>

              <!-- Deskripsi -->
              <div class="md:col-span-2">
                <label class="text-xs text-gray-500 mb-1.5 block font-medium">
                  Deskripsi Barang <span class="text-red-400">*</span>
                </label>
                <textarea
                  v-model="form.description"
                  rows="5"
                  placeholder="Ceritakan sejarah barang, bahan, ukuran, keunikan, kondisi fisik..."
                  :class="inputClass(errors.description) + ' resize-none'"
                ></textarea>
                <div class="flex justify-between mt-1">
                  <p
                    v-if="errors.description"
                    class="field-error-msg text-red-500 text-xs"
                  >
                    {{ errors.description }}
                  </p>
                  <span class="text-xs text-gray-300 ml-auto"
                    >{{ (form.description || "").length }} karakter</span
                  >
                </div>
              </div>
            </div>
          </div>

          <!-- ── SECTION 2: Galeri Foto ────────────────────────────── -->
          <div
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
          >
            <div
              class="px-6 py-5 border-b border-gray-100 flex items-center justify-between"
            >
              <div class="flex items-center gap-3">
                <div
                  class="w-8 h-8 rounded-lg bg-black flex items-center justify-center shrink-0"
                >
                  <svg
                    class="w-4 h-4 text-white"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                </div>
                <div>
                  <p class="font-semibold text-sm">Galeri Foto</p>
                  <p class="text-xs text-gray-400 mt-0.5">
                    Foto utama dan tambahan (maks. 10)
                  </p>
                </div>
              </div>
              <span
                class="text-xs text-gray-400 bg-gray-50 px-3 py-1 rounded-full border border-gray-100"
              >
                {{ totalPhotoCount }}/10
              </span>
            </div>

            <div class="px-6 py-6 flex flex-col gap-5">
              <!-- Error foto -->
              <p
                v-if="errors.photos"
                class="field-error-msg text-red-500 text-xs"
              >
                {{ errors.photos }}
              </p>

              <!-- Foto yang sudah ada -->
              <div v-if="existingPhotos.length > 0">
                <p class="text-xs font-medium text-gray-500 mb-3">
                  Foto Saat Ini
                </p>
                <div
                  class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3"
                >
                  <div
                    v-for="(photo, idx) in existingPhotos"
                    :key="photo.id"
                    class="relative group rounded-xl overflow-hidden border-2 transition-all duration-200"
                    :class="photo.isMain ? 'border-black' : 'border-gray-100'"
                  >
                    <div class="aspect-square overflow-hidden bg-gray-100">
                      <img
                        :src="photo.url"
                        :alt="'Foto ' + (idx + 1)"
                        class="w-full h-full object-cover"
                      />
                    </div>

                    <!-- Main badge -->
                    <div
                      v-if="photo.isMain"
                      class="absolute top-2 left-2 bg-black text-white text-[10px] font-semibold px-2 py-0.5 rounded-full"
                    >
                      Utama
                    </div>

                    <!-- Overlay actions -->
                    <div
                      class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-center justify-center gap-1.5 opacity-0 group-hover:opacity-100"
                    >
                      <!-- Fullscreen -->
                      <button
                        @click="fullscreenPhoto = photo.url"
                        class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-gray-700 hover:bg-gray-100 transition-colors"
                        title="Preview"
                      >
                        <svg
                          class="w-4 h-4"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"
                          />
                        </svg>
                      </button>

                      <!-- Move left -->
                      <button
                        v-if="idx > 0"
                        @click="movePhoto(idx, -1)"
                        class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-gray-700 hover:bg-gray-100 transition-colors"
                        title="Pindah ke kiri"
                      >
                        <svg
                          class="w-4 h-4"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 19l-7-7 7-7"
                          />
                        </svg>
                      </button>

                      <!-- Move right -->
                      <button
                        v-if="idx < existingPhotos.length - 1"
                        @click="movePhoto(idx, 1)"
                        class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-gray-700 hover:bg-gray-100 transition-colors"
                        title="Pindah ke kanan"
                      >
                        <svg
                          class="w-4 h-4"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                          />
                        </svg>
                      </button>

                      <!-- Delete -->
                      <button
                        @click="markPhotoForDeletion(photo)"
                        class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center text-white hover:bg-red-600 transition-colors"
                        title="Hapus foto"
                      >
                        <svg
                          class="w-4 h-4"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                          />
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
                <p class="text-xs text-gray-400 mt-2">
                  Foto pertama (kiri atas) otomatis menjadi foto utama. Gunakan
                  tombol panah untuk mengubah urutan.
                </p>
              </div>

              <!-- Foto baru yang ditambahkan -->
              <div v-if="newPhotoPreviews.length > 0">
                <p class="text-xs font-medium text-gray-500 mb-3">
                  Foto Baru (Belum Tersimpan)
                </p>
                <div
                  class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3"
                >
                  <div
                    v-for="(preview, idx) in newPhotoPreviews"
                    :key="'new-' + idx"
                    class="relative group rounded-xl overflow-hidden border-2 border-dashed border-gray-300"
                  >
                    <div class="aspect-square overflow-hidden bg-gray-50">
                      <img :src="preview" class="w-full h-full object-cover" />
                    </div>
                    <div
                      class="absolute top-2 left-2 bg-amber-400 text-black text-[10px] font-semibold px-2 py-0.5 rounded-full"
                    >
                      Baru
                    </div>
                    <button
                      @click="removeNewPhoto(idx)"
                      class="absolute top-2 right-2 w-7 h-7 bg-red-500 rounded-lg flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity"
                    >
                      <svg
                        class="w-3.5 h-3.5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"
                        />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Upload area -->
              <div v-if="totalPhotoCount < 10">
                <div
                  @dragover.prevent="newPhotoDragOver = true"
                  @dragleave="newPhotoDragOver = false"
                  @drop.prevent="handleNewPhotoDrop"
                  @click="$refs.photoInput.click()"
                  :class="[
                    'border-2 border-dashed rounded-2xl py-10 flex flex-col items-center gap-3 cursor-pointer transition-all duration-200',
                    newPhotoDragOver
                      ? 'border-black bg-gray-50 scale-[1.01]'
                      : 'border-gray-200 hover:border-gray-400',
                  ]"
                >
                  <input
                    ref="photoInput"
                    type="file"
                    accept="image/*"
                    multiple
                    class="hidden"
                    @change="handleNewPhotoInput"
                  />
                  <div
                    class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center"
                  >
                    <svg
                      class="w-6 h-6 text-gray-400"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M12 4v16m8-8H4"
                      />
                    </svg>
                  </div>
                  <div class="text-center">
                    <p class="text-sm font-medium text-gray-700">
                      Tambah Foto Baru
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                      PNG, JPG, WEBP — Maks. 10MB per foto (sisa
                      {{ 10 - totalPhotoCount }} slot)
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ── SECTION 3: Pengaturan Harga ─────────────────────── -->
          <div
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
          >
            <div
              class="px-6 py-5 border-b border-gray-100 flex items-center gap-3"
            >
              <div
                class="w-8 h-8 rounded-lg bg-black flex items-center justify-center shrink-0"
              >
                <svg
                  class="w-4 h-4 text-white"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
              <div>
                <p class="font-semibold text-sm">Pengaturan Harga</p>
                <p class="text-xs text-gray-400 mt-0.5">
                  Harga awal dan kelipatan bid
                </p>
              </div>
            </div>

            <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-5">
              <!-- Harga Awal -->
              <div>
                <label class="text-xs text-gray-500 mb-1.5 block font-medium">
                  Harga Awal <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                  <span
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-medium"
                    >Rp</span
                  >
                  <input
                    v-model="form.startPrice"
                    type="number"
                    placeholder="5.000.000"
                    :class="inputClass(errors.startPrice) + ' pl-10'"
                  />
                </div>
                <p
                  v-if="errors.startPrice"
                  class="field-error-msg text-red-500 text-xs mt-1"
                >
                  {{ errors.startPrice }}
                </p>
                <p
                  v-else-if="form.startPrice"
                  class="text-xs text-gray-400 mt-1"
                >
                  {{ formatRupiah(form.startPrice) }}
                </p>
              </div>

              <!-- Kelipatan Minimum -->
              <div>
                <label class="text-xs text-gray-500 mb-1.5 block font-medium">
                  Kelipatan Minimum Penawaran
                  <span class="text-red-400">*</span>
                </label>
                <div class="flex gap-2">
                  <button
                    v-for="inc in minIncrementOptions"
                    :key="inc"
                    @click="form.minIncrement = inc"
                    :class="[
                      'flex-1 py-2 text-xs rounded-lg border font-medium transition-all duration-200',
                      form.minIncrement === inc
                        ? 'bg-black text-white border-black'
                        : 'border-gray-200 text-gray-600 hover:border-black hover:text-black',
                    ]"
                  >
                    {{ formatRupiahShort(inc) }}
                  </button>
                </div>
                <p
                  v-if="errors.minIncrement"
                  class="field-error-msg text-red-500 text-xs mt-1"
                >
                  {{ errors.minIncrement }}
                </p>
              </div>

              <!-- Buy Now toggle -->
              <div class="md:col-span-2">
                <div class="border border-gray-200 rounded-xl p-5">
                  <div class="flex items-center justify-between mb-1">
                    <div>
                      <p class="text-sm font-medium text-gray-800">
                        Harga Beli Sekarang (Buy Now)
                      </p>
                      <p class="text-xs text-gray-400 mt-0.5">
                        Jika peserta memilih Buy Now, lelang langsung berakhir
                        dan barang dimenangkan.
                      </p>
                    </div>
                    <button
                      @click="form.buyNowEnabled = !form.buyNowEnabled"
                      :class="[
                        'relative w-11 h-6 rounded-full transition-all duration-200 shrink-0',
                        form.buyNowEnabled ? 'bg-black' : 'bg-gray-200',
                      ]"
                    >
                      <span
                        :class="[
                          'absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-all duration-200',
                          form.buyNowEnabled ? 'left-[22px]' : 'left-0.5',
                        ]"
                      ></span>
                    </button>
                  </div>

                  <transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 -translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                  >
                    <div v-if="form.buyNowEnabled" class="mt-4">
                      <label
                        class="text-xs text-gray-500 mb-1.5 block font-medium"
                      >
                        Harga Buy Now <span class="text-red-400">*</span>
                      </label>
                      <div class="relative">
                        <span
                          class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-medium"
                          >Rp</span
                        >
                        <input
                          v-model="form.buyNowPrice"
                          type="number"
                          placeholder="20.000.000"
                          :class="inputClass(errors.buyNowPrice) + ' pl-10'"
                        />
                      </div>
                      <p
                        v-if="errors.buyNowPrice"
                        class="field-error-msg text-red-500 text-xs mt-1"
                      >
                        {{ errors.buyNowPrice }}
                      </p>
                      <p
                        v-else-if="form.buyNowPrice"
                        class="text-xs text-gray-400 mt-1"
                      >
                        {{ formatRupiah(form.buyNowPrice) }}
                      </p>
                    </div>
                  </transition>
                </div>
              </div>
            </div>
          </div>

          <!-- ── SECTION 4: Pengaturan Waktu ────────────────────── -->
          <div
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
          >
            <div
              class="px-6 py-5 border-b border-gray-100 flex items-center gap-3"
            >
              <div
                class="w-8 h-8 rounded-lg bg-black flex items-center justify-center shrink-0"
              >
                <svg
                  class="w-4 h-4 text-white"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
              <div>
                <p class="font-semibold text-sm">Pengaturan Waktu</p>
                <p class="text-xs text-gray-400 mt-0.5">
                  Jadwal mulai dan berakhirnya lelang
                </p>
              </div>
            </div>

            <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-5">
              <!-- Waktu Mulai -->
              <div>
                <label class="text-xs text-gray-500 mb-1.5 block font-medium">
                  Tanggal & Jam Mulai <span class="text-red-400">*</span>
                </label>
                <div class="flex gap-2">
                  <input
                    v-model="form.startDate"
                    type="date"
                    :class="inputClass(errors.startDate) + ' flex-1'"
                    :min="todayStr"
                  />
                  <input
                    v-model="form.startTime"
                    type="time"
                    :class="inputClass(errors.startDate) + ' w-32'"
                  />
                </div>
                <p
                  v-if="errors.startDate"
                  class="field-error-msg text-red-500 text-xs mt-1"
                >
                  {{ errors.startDate }}
                </p>
              </div>

              <!-- Waktu Berakhir -->
              <div>
                <label class="text-xs text-gray-500 mb-1.5 block font-medium">
                  Tanggal & Jam Berakhir <span class="text-red-400">*</span>
                </label>
                <div class="flex gap-2">
                  <input
                    v-model="form.endDate"
                    type="date"
                    :class="inputClass(errors.endDate) + ' flex-1'"
                    :min="form.startDate || todayStr"
                  />
                  <input
                    v-model="form.endTime"
                    type="time"
                    :class="inputClass(errors.endDate) + ' w-32'"
                  />
                </div>
                <p
                  v-if="errors.endDate"
                  class="field-error-msg text-red-500 text-xs mt-1"
                >
                  {{ errors.endDate }}
                </p>
              </div>

              <!-- Durasi summary -->
              <div v-if="auctionDuration" class="md:col-span-2">
                <div
                  class="bg-gray-50 rounded-xl px-5 py-4 flex items-center gap-4 border border-gray-100"
                >
                  <svg
                    class="w-5 h-5 text-gray-400 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                  <div>
                    <p class="text-xs text-gray-400">Estimasi Durasi Lelang</p>
                    <p class="text-sm font-bold mt-0.5">
                      {{ auctionDuration }}
                    </p>
                  </div>
                  <span
                    class="ml-auto text-xs font-medium bg-amber-100 text-amber-700 px-3 py-1.5 rounded-full"
                  >
                    🕐 Akan Datang
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- ── ACTION BUTTONS ────────────────────────────────────── -->
          <div class="flex flex-col sm:flex-row gap-3 pb-10">
            <!-- Hapus -->
            <button
              @click="openDeleteModal"
              class="sm:w-40 py-3.5 border-2 border-red-200 text-red-500 rounded-xl text-sm font-semibold hover:bg-red-50 hover:border-red-400 transition-all duration-200 flex items-center justify-center gap-2"
            >
              <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                />
              </svg>
              Hapus Lelang
            </button>

            <!-- Batal -->
            <button
              @click="router.push('/my-auctions')"
              class="sm:w-40 py-3.5 border-2 border-gray-200 text-gray-600 rounded-xl text-sm font-semibold hover:border-black hover:text-black transition-all duration-200"
            >
              Batal
            </button>

            <!-- Simpan -->
            <button
              @click="openSaveModal"
              :disabled="isSubmitting"
              class="flex-1 py-3.5 bg-black text-white rounded-xl text-sm font-semibold hover:bg-gray-800 transition-colors disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
              <svg
                v-if="isSubmitting"
                class="w-4 h-4 animate-spin"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                />
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                />
              </svg>
              {{ isSubmitting ? "Menyimpan..." : "Simpan Perubahan" }}
            </button>
          </div>
        </div>

        <!-- ── SIDEBAR: Ringkasan ─────────────────────────────────── -->
        <aside
          class="hidden xl:flex flex-col gap-4 w-72 shrink-0 sticky top-24"
        >
          <!-- Preview foto utama -->
          <div
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
          >
            <div class="aspect-video bg-gray-100 overflow-hidden">
              <img
                v-if="existingPhotos[0]?.url || newPhotoPreviews[0]"
                :src="existingPhotos[0]?.url || newPhotoPreviews[0]"
                class="w-full h-full object-cover"
              />
              <div
                v-else
                class="w-full h-full flex items-center justify-center"
              >
                <svg
                  class="w-10 h-10 text-gray-200"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
              </div>
            </div>

            <!-- Ringkasan -->
            <div class="p-5">
              <p class="text-xs text-gray-400 mb-0.5">
                {{ form.category || "Kategori belum dipilih" }}
              </p>
              <p class="font-semibold text-sm leading-snug mb-4">
                {{ form.name || "Nama barang belum diisi" }}
              </p>
              <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                  <p class="text-xs text-gray-400">Harga Awal</p>
                  <p class="text-xs font-semibold">
                    {{ form.startPrice ? formatRupiah(form.startPrice) : "—" }}
                  </p>
                </div>
                <div class="flex items-center justify-between">
                  <p class="text-xs text-gray-400">Kelipatan Bid</p>
                  <p class="text-xs font-semibold">
                    {{
                      form.minIncrement ? formatRupiah(form.minIncrement) : "—"
                    }}
                  </p>
                </div>
                <div class="flex items-center justify-between">
                  <p class="text-xs text-gray-400">Harga Buy Now</p>
                  <p class="text-xs font-semibold">
                    {{
                      form.buyNowEnabled && form.buyNowPrice
                        ? formatRupiah(form.buyNowPrice)
                        : "—"
                    }}
                  </p>
                </div>
                <div class="border-t border-gray-100 pt-2.5">
                  <div class="flex items-center justify-between mb-1">
                    <p class="text-xs text-gray-400">Waktu Mulai</p>
                    <p class="text-xs font-semibold">
                      {{ formatDateTime(form.startDate, form.startTime) }}
                    </p>
                  </div>
                  <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-400">Waktu Berakhir</p>
                    <p class="text-xs font-semibold">
                      {{ formatDateTime(form.endDate, form.endTime) }}
                    </p>
                  </div>
                </div>
                <div
                  v-if="auctionDuration"
                  class="flex items-center justify-between pt-1"
                >
                  <p class="text-xs text-gray-400">Durasi</p>
                  <p class="text-xs font-semibold">{{ auctionDuration }}</p>
                </div>
              </div>

              <div class="mt-4">
                <span
                  class="text-xs font-medium px-3 py-1.5 rounded-full bg-amber-100 text-amber-700"
                >
                  🕐 Akan Datang
                </span>
              </div>
            </div>
          </div>

          <!-- Quick tips -->
          <div class="bg-gray-50 rounded-2xl border border-gray-100 p-5">
            <p
              class="text-xs font-semibold tracking-[0.15em] uppercase text-gray-400 mb-3"
            >
              Tips
            </p>
            <ul class="space-y-2">
              <li
                v-for="tip in [
                  'Foto berkualitas tinggi meningkatkan harga akhir hingga 40%.',
                  'Deskripsi jelas membangun kepercayaan kolektor.',
                  'Jadwalkan di jam prime-time (19.00–22.00 WITA).',
                ]"
                :key="tip"
                class="flex items-start gap-2 text-xs text-gray-500 leading-relaxed"
              >
                <span class="text-black mt-0.5 shrink-0">•</span>
                {{ tip }}
              </li>
            </ul>
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>

<style scoped>
.font-sans {
  font-family: "DM Sans", sans-serif;
}
.animate-spin {
  animation: spin 0.8s linear infinite;
}
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
