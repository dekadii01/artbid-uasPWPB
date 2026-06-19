<script setup>
import { Icon } from "@iconify/vue";
import { ref, computed, onMounted } from "vue";
import { getCategories } from "../../api/categories";
import { useRouter } from "vue-router";
import { createAuction } from "../../api/auctions";
import { buildAuctionFormData } from "../../api/buildAuctionFormData";

const router = useRouter();

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
const categories = ref([]);

async function fetchCategories() {
  try {
    const { data } = await getCategories();
    categories.value = data.map((c) => c.name);
  } catch (err) {
    console.error("Gagal memuat kategori:", err);
    categories.value = [
      "Lukisan",
      "Patung",
      "Topeng Tradisional",
      "Ukiran Kayu",
      "Kerajinan Perak",
      "Barang Antik",
      "Koleksi Langka",
      "Lainnya",
    ];
  }
}

onMounted(() => {
  fetchCategories();
});
const conditions = ["Sangat Baik", "Baik", "Cukup Baik", "Perlu Restorasi"];
const minIncrementOptions = [50000, 100000, 250000, 500000, 1000000];
const todayStr = new Date().toISOString().split("T")[0];

const steps = [
  { label: "Informasi Barang" },
  { label: "Galeri Foto" },
  { label: "Pengaturan" },
  { label: "Publikasi" },
];

const tips = [
  "Foto berkualitas tinggi dapat meningkatkan harga akhir hingga 40%.",
  "Deskripsi detail dan jujur membangun kepercayaan kolektor.",
  "Aktifkan Anti-Sniping agar lelang lebih fair dan kompetitif.",
  "Jadwalkan lelang di jam prime-time (19.00–22.00 WITA).",
];

// ─── Form state ───────────────────────────────────────────────────
const form = ref({
  name: "",
  category: "",
  artist: "",
  year: "",
  condition: "",
  description: "",
  mainPhoto: null,
  extraPhotos: [],
  startPrice: "",
  minIncrement: 100000,
  buyNowPrice: "",
  startDate: "",
  startTime: "10:00",
  endDate: "",
  endTime: "10:00",
  agreed: false,
});

const errors = ref({});
const isSubmitting = ref(false);
const isSuccess = ref(false);
const mainPhotoPreview = ref(null);
const extraPhotoPreviews = ref([]);
const mainDragOver = ref(false);

// Dipakai goToDetail() untuk redirect ke lelang yang baru dibuat
const createdAuctionId = ref(null);

// Accordion state — all open by default
const openSections = ref([true, true, true]);
function toggleSection(idx) {
  openSections.value[idx] = !openSections.value[idx];
}

// ─── Progress: which step is active ──────────────────────────────
const currentStep = computed(() => {
  if (!sectionDone.value[0]) return 0;
  if (!sectionDone.value[1]) return 1;
  if (!sectionDone.value[2]) return 2;
  return 3;
});

const sectionDone = computed(() => [
  !!(
    form.value.name &&
    form.value.category &&
    form.value.condition &&
    form.value.description
  ),
  !!mainPhotoPreview.value,
  !!(
    form.value.startPrice &&
    form.value.minIncrement &&
    form.value.startDate &&
    form.value.endDate
  ),
]);

// ─── Completion checklist ─────────────────────────────────────────
const completionChecks = computed(() => [
  { label: "Nama barang", done: !!form.value.name },
  { label: "Kategori", done: !!form.value.category },
  { label: "Kondisi", done: !!form.value.condition },
  { label: "Deskripsi", done: form.value.description.length >= 30 },
  { label: "Foto utama", done: !!mainPhotoPreview.value },
  { label: "Harga awal", done: !!form.value.startPrice },
  {
    label: "Jadwal lelang",
    done: !!(form.value.startDate && form.value.endDate),
  },
  { label: "Persetujuan", done: form.value.agreed },
]);
const completionPct = computed(() =>
  Math.round(
    (completionChecks.value.filter((c) => c.done).length /
      completionChecks.value.length) *
      100,
  ),
);

// ─── Auction duration ─────────────────────────────────────────────
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

const auctionStartsNow = computed(() => {
  if (!form.value.startDate) return false;
  const start = new Date(`${form.value.startDate}T${form.value.startTime}`);
  return start <= new Date();
});

// ─── Photo handlers ───────────────────────────────────────────────
function handleMainFile(e) {
  const file = e.target.files[0];
  if (!file) return;
  form.value.mainPhoto = file;
  mainPhotoPreview.value = URL.createObjectURL(file);
}
function handleMainDrop(e) {
  mainDragOver.value = false;
  const file = e.dataTransfer.files[0];
  if (!file || !file.type.startsWith("image/")) return;
  form.value.mainPhoto = file;
  mainPhotoPreview.value = URL.createObjectURL(file);
}
function handleExtraFiles(e) {
  const files = Array.from(e.target.files);
  const remaining = 5 - extraPhotoPreviews.value.length;
  files.slice(0, remaining).forEach((f) => {
    if (!f.type.startsWith("image/")) return;
    extraPhotoPreviews.value.push(URL.createObjectURL(f));
    form.value.extraPhotos.push(f);
  });
}
function removeExtraPhoto(idx) {
  extraPhotoPreviews.value.splice(idx, 1);
  form.value.extraPhotos.splice(idx, 1);
}

// ─── Validation ───────────────────────────────────────────────────
function validate() {
  const e = {};
  if (!form.value.name) e.name = "Nama barang wajib diisi.";
  if (!form.value.category) e.category = "Pilih kategori barang.";
  if (!form.value.condition) e.condition = "Pilih kondisi barang.";
  if (form.value.description.length < 30)
    e.description = "Deskripsi minimal 30 karakter.";
  if (!mainPhotoPreview.value) e.mainPhoto = "Foto utama wajib diunggah.";
  if (!form.value.startPrice || form.value.startPrice <= 0)
    e.startPrice = "Masukkan harga awal yang valid.";
  if (!form.value.startDate) e.startDate = "Tanggal mulai wajib diisi.";
  if (!form.value.endDate) e.endDate = "Tanggal berakhir wajib diisi.";
  if (form.value.startDate && form.value.endDate) {
    const start = new Date(`${form.value.startDate}T${form.value.startTime}`);
    const end = new Date(`${form.value.endDate}T${form.value.endTime}`);
    if (end <= start) e.endDate = "Waktu berakhir harus setelah waktu mulai.";
  }
  if (!form.value.agreed)
    e.agreed = "Anda harus menyetujui syarat & ketentuan.";
  errors.value = e;
  return Object.keys(e).length === 0;
}

/**
 * Map error validasi dari Laravel (response.data.errors, field snake_case)
 * ke struktur errors.value lokal (field camelCase yang dipakai form ini).
 */
function applyServerErrors(serverErrors) {
  const map = {
    title: "name",
    category: "category",
    description: "description",
    condition: "condition",
    main_photo: "mainPhoto",
    extra_photos: "extraPhotos",
    starting_price: "startPrice",
    bid_increment: "minIncrement",
    buy_now_price: "buyNowPrice",
    starts_at: "startDate",
    ends_at: "endDate",
  };

  const mapped = {};
  for (const [serverField, messages] of Object.entries(serverErrors)) {
    if (map[serverField]) {
      mapped[map[serverField]] = messages[0];
    } else {
      // Log unmapped errors agar terlihat di console saat debugging
      console.warn(`[422] Unmapped validation error — ${serverField}:`, messages);
    }
  }
  errors.value = { ...errors.value, ...mapped };
}

// ─── Submit ───────────────────────────────────────────────────────
async function publishAuction() {
  if (!validate()) {
    setTimeout(() => {
      const el = document.querySelector(".field-error");
      el?.scrollIntoView({ behavior: "smooth", block: "center" });
    }, 50);
    return;
  }

  isSubmitting.value = true;

  try {
    const formData = buildAuctionFormData(form.value);
    const { data } = await createAuction(formData);

    createdAuctionId.value = data.auction.id;
    isSuccess.value = true;
  } catch (err) {
    if (err.response?.status === 422) {
      applyServerErrors(err.response.data.errors);
      setTimeout(() => {
        const el = document.querySelector(".field-error");
        el?.scrollIntoView({ behavior: "smooth", block: "center" });
      }, 50);
    } else {
      alert(
        err.response?.data?.message ||
          "Gagal membuat lelang. Silakan coba lagi.",
      );
    }
  } finally {
    isSubmitting.value = false;
  }
}

function saveDraft() {
  // TODO: belum ada endpoint draft di backend.
  // Untuk sekarang, fitur draft belum tersedia.
  alert("Fitur simpan draft belum tersedia.");
}

function goToDetail() {
  isSuccess.value = false;
  router.push(`/auction/${createdAuctionId.value}`);
}

function resetForm() {
  isSuccess.value = false;
  form.value = {
    name: "",
    category: "",
    artist: "",
    year: "",
    condition: "",
    description: "",
    mainPhoto: null,
    extraPhotos: [],
    startPrice: "",
    minIncrement: 100000,
    buyNowPrice: "",
    startDate: "",
    startTime: "10:00",
    endDate: "",
    endTime: "10:00",
    agreed: false,
  };
  mainPhotoPreview.value = null;
  extraPhotoPreviews.value = [];
  errors.value = {};
  createdAuctionId.value = null;
}
</script>

<template>
  <div class="bg-gray-50 min-h-screen font-sans">
    <!-- ── SUCCESS STATE ──────────────────────────────────────────── -->
    <transition
      enter-active-class="transition duration-400 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
    >
      <div
        v-if="isSuccess"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-6"
      >
        <div
          class="bg-white rounded-3xl p-10 max-w-md w-full text-center shadow-2xl"
        >
          <div
            class="w-20 h-20 bg-black rounded-2xl flex items-center justify-center text-4xl mx-auto mb-6"
          >
            <Icon icon="mdi:check" class="text-white w-10 h-10" />
          </div>
          <h2 class="text-2xl font-bold mb-2">Lelang Berhasil Dibuat</h2>
          <p class="text-gray-500 text-sm leading-relaxed mb-2">
            Lelang Anda akan mulai pada
          </p>
          <p class="text-black font-semibold text-base mb-8">
            {{ formatDateTime(form.startDate, form.startTime) }} WITA
          </p>
          <div class="flex flex-col gap-3">
            <button
              @click="goToDetail"
              class="w-full py-3.5 bg-black text-white rounded-xl text-sm font-semibold hover:bg-gray-800 transition-colors"
            >
              Lihat Detail Lelang
            </button>
            <button
              @click="resetForm"
              class="w-full py-3 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:border-black hover:text-black transition-colors"
            >
              Buat Lelang Lain
            </button>
          </div>
        </div>
      </div>
    </transition>

    <div class="px-6 md:px-10 py-8 mx-auto">
      <!-- ── HEADER ─────────────────────────────────────────────────── -->
      <nav class="flex items-center gap-2 text-sm text-gray-400 mb-8">
        <router-link to="/" class="hover:text-black transition-colors"
          >Beranda</router-link
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
            d="M9 5l7 7-7 7"
          />
        </svg>
        <router-link to="/auctions" class="hover:text-black transition-colors"
          >Daftar Lelang</router-link
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
            d="M9 5l7 7-7 7"
          />
        </svg>
        <span class="text-gray-700 font-medium">Buat Lelang</span>
      </nav>

      <div class="mb-8">
        <span
          class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400"
          >Formulir</span
        >
        <h1 class="text-3xl md:text-4xl font-bold mt-2">Buat Lelang Baru</h1>
        <p class="text-gray-500 mt-3 text-sm max-w-xl leading-relaxed">
          Daftarkan barang seni Anda dan mulai proses lelang secara online.
          Lengkapi informasi barang dengan jelas agar menarik minat para
          kolektor.
        </p>
      </div>

      <!-- ── PROGRESS STEPS ─────────────────────────────────────────── -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-10">
        <div
          v-for="(step, idx) in steps"
          :key="step.label"
          :class="[
            'rounded-xl px-4 py-3 flex items-center gap-3 border transition-all duration-300',
            currentStep > idx
              ? 'bg-black text-white border-black'
              : currentStep === idx
                ? 'bg-white border-black shadow-sm'
                : 'bg-white border-gray-100 text-gray-400',
          ]"
        >
          <div
            :class="[
              'w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold shrink-0',
              currentStep > idx
                ? 'bg-white text-black'
                : currentStep === idx
                  ? 'bg-black text-white'
                  : 'bg-gray-100 text-gray-400',
            ]"
          >
            <svg
              v-if="currentStep > idx"
              class="w-3.5 h-3.5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2.5"
                d="M5 13l4 4L19 7"
              />
            </svg>
            <span v-else>{{ idx + 1 }}</span>
          </div>
          <span
            :class="[
              'text-xs font-medium',
              currentStep === idx ? 'text-black' : '',
            ]"
            >{{ step.label }}</span
          >
        </div>
      </div>

      <!-- ── MAIN LAYOUT ─────────────────────────────────────────────── -->
      <div class="flex gap-8 items-start">
        <!-- FORM SECTIONS ────────────────────────────────────────────── -->
        <div class="flex-1 min-w-0 flex flex-col gap-6">
          <!-- ── SECTION 1: Informasi Barang ─────────────────────────── -->
          <div
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
            :class="{ '': currentStep === 0 }"
          >
            <div
              class="px-6 py-5 border-b border-gray-100 flex items-center justify-between cursor-pointer"
              @click="toggleSection(0)"
            >
              <div class="flex items-center gap-3">
                <div
                  :class="[
                    'w-8 h-8 rounded-lg flex items-center justify-center text-sm shrink-0',
                    sectionDone[0]
                      ? 'bg-black text-white'
                      : 'bg-gray-100 text-gray-500',
                  ]"
                >
                  <svg
                    v-if="sectionDone[0]"
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.5"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                  <span v-else class="font-bold">1</span>
                </div>
                <div>
                  <p class="font-semibold text-sm">Informasi Barang</p>
                  <p class="text-xs text-gray-400 mt-0.5">
                    Nama, kategori, dan deskripsi
                  </p>
                </div>
              </div>
              <svg
                :class="[
                  'w-4 h-4 text-gray-400 transition-transform duration-200',
                  openSections[0] ? 'rotate-180' : '',
                ]"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </div>

            <div
              v-show="openSections[0]"
              class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-5"
            >
              <!-- Nama Barang -->
              <div class="md:col-span-2">
                <label class="field-label"
                  >Nama Barang Seni <span class="text-red-400">*</span></label
                >
                <input
                  v-model="form.name"
                  type="text"
                  placeholder="Contoh: Lukisan Bali Klasik Tahun 1980"
                  :class="['field-input', errors.name ? 'field-error' : '']"
                />
                <p v-if="errors.name" class="field-error-msg">
                  {{ errors.name }}
                </p>
              </div>

              <!-- Kategori -->
              <div>
                <label class="field-label"
                  >Kategori <span class="text-red-400">*</span></label
                >
                <select
                  v-model="form.category"
                  :class="['field-input', errors.category ? 'field-error' : '']"
                >
                  <option value="">Pilih Kategori</option>
                  <option v-for="cat in categories" :key="cat" :value="cat">
                    {{ cat }}
                  </option>
                </select>
                <p v-if="errors.category" class="field-error-msg">
                  {{ errors.category }}
                </p>
              </div>

              <!-- Kondisi -->
              <div>
                <label class="field-label"
                  >Kondisi Barang <span class="text-red-400">*</span></label
                >
                <select
                  v-model="form.condition"
                  :class="[
                    'field-input',
                    errors.condition ? 'field-error' : '',
                  ]"
                >
                  <option value="">Pilih Kondisi</option>
                  <option v-for="c in conditions" :key="c" :value="c">
                    {{ c }}
                  </option>
                </select>
                <p v-if="errors.condition" class="field-error-msg">
                  {{ errors.condition }}
                </p>
              </div>

              <!-- Seniman -->
              <div>
                <label class="field-label"
                  >Seniman / Pembuat
                  <span class="text-gray-300 font-normal"
                    >(Opsional)</span
                  ></label
                >
                <input
                  v-model="form.artist"
                  type="text"
                  placeholder="Contoh: I Wayan Sudarsana"
                  class="field-input"
                />
              </div>

              <!-- Tahun -->
              <div>
                <label class="field-label"
                  >Tahun Pembuatan
                  <span class="text-gray-300 font-normal"
                    >(Opsional)</span
                  ></label
                >
                <input
                  v-model="form.year"
                  type="number"
                  placeholder="Contoh: 1980"
                  min="1000"
                  max="2099"
                  class="field-input"
                />
              </div>

              <!-- Deskripsi -->
              <div class="md:col-span-2">
                <label class="field-label"
                  >Deskripsi Barang <span class="text-red-400">*</span></label
                >
                <textarea
                  v-model="form.description"
                  rows="6"
                  placeholder="Ceritakan sejarah barang, bahan, ukuran, keunikan, kondisi fisik, dan apakah dilengkapi sertifikat keaslian..."
                  :class="[
                    'field-input resize-none',
                    errors.description ? 'field-error' : '',
                  ]"
                ></textarea>
                <div class="flex justify-between mt-1">
                  <p v-if="errors.description" class="field-error-msg">
                    {{ errors.description }}
                  </p>
                  <span class="text-xs text-gray-300 ml-auto"
                    >{{ form.description.length }} karakter</span
                  >
                </div>
              </div>
            </div>
          </div>

          <!-- ── SECTION 2: Galeri Foto ───────────────────────────────── -->
          <div
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
            :class="{ 'ring-2 ring-black ring-offset-2': currentStep === 1 }"
          >
            <div
              class="px-6 py-5 border-b border-gray-100 flex items-center justify-between cursor-pointer"
              @click="toggleSection(1)"
            >
              <div class="flex items-center gap-3">
                <div
                  :class="[
                    'w-8 h-8 rounded-lg flex items-center justify-center text-sm shrink-0',
                    sectionDone[1]
                      ? 'bg-black text-white'
                      : 'bg-gray-100 text-gray-500',
                  ]"
                >
                  <svg
                    v-if="sectionDone[1]"
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.5"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                  <span v-else class="font-bold">2</span>
                </div>
                <div>
                  <p class="font-semibold text-sm">Galeri Foto</p>
                  <p class="text-xs text-gray-400 mt-0.5">
                    Foto utama dan tambahan
                  </p>
                </div>
              </div>
              <svg
                :class="[
                  'w-4 h-4 text-gray-400 transition-transform duration-200',
                  openSections[1] ? 'rotate-180' : '',
                ]"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </div>

            <div v-show="openSections[1]" class="px-6 py-6 flex flex-col gap-5">
              <!-- Foto Utama drop zone -->
              <div>
                <label class="field-label"
                  >Foto Utama <span class="text-red-400">*</span></label
                >
                <div
                  @dragover.prevent="mainDragOver = true"
                  @dragleave="mainDragOver = false"
                  @drop.prevent="handleMainDrop"
                  @click="$refs.mainInput.click()"
                  :class="[
                    'relative border-2 border-dashed rounded-2xl cursor-pointer transition-all duration-200',
                    mainDragOver
                      ? 'border-black bg-gray-50 scale-[1.01]'
                      : 'border-gray-200 hover:border-gray-400',
                    errors.mainPhoto ? 'border-red-300 bg-red-50' : '',
                  ]"
                >
                  <input
                    ref="mainInput"
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="handleMainFile"
                  />

                  <!-- Preview -->
                  <div
                    v-if="mainPhotoPreview"
                    class="relative aspect-video rounded-2xl overflow-hidden"
                  >
                    <img
                      :src="mainPhotoPreview"
                      class="w-full h-full object-cover"
                    />
                    <div
                      class="absolute inset-0 bg-black/0 hover:bg-black/30 transition-colors flex items-center justify-center group"
                    >
                      <span
                        class="text-white text-sm font-medium opacity-0 group-hover:opacity-100 transition-opacity"
                        >Ganti Foto</span
                      >
                    </div>
                    <button
                      @click.stop="
                        mainPhotoPreview = null;
                        form.mainPhoto = null;
                      "
                      class="absolute top-3 right-3 w-7 h-7 bg-black/60 backdrop-blur-sm rounded-lg flex items-center justify-center text-white hover:bg-black transition-colors"
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

                  <!-- Empty state -->
                  <div
                    v-else
                    class="py-14 flex flex-col items-center gap-3 text-center px-6"
                  >
                    <div
                      class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center"
                    >
                      <svg
                        class="w-7 h-7 text-gray-400"
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
                    <div>
                      <p class="text-sm font-medium text-gray-700">
                        Seret foto ke sini atau klik untuk mengunggah
                      </p>
                      <p class="text-xs text-gray-400 mt-1">
                        PNG, JPG, WEBP — Maks. 10MB
                      </p>
                    </div>
                  </div>
                </div>
                <p v-if="errors.mainPhoto" class="field-error-msg">
                  {{ errors.mainPhoto }}
                </p>
              </div>

              <!-- Foto Tambahan -->
              <div>
                <div class="flex items-center justify-between mb-2">
                  <label class="field-label mb-0"
                    >Foto Tambahan
                    <span class="text-gray-300 font-normal"
                      >(Maks. 5 foto)</span
                    ></label
                  >
                  <span class="text-xs text-gray-400"
                    >{{ extraPhotoPreviews.length }}/5</span
                  >
                </div>
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
                  <!-- Existing extra photos -->
                  <div
                    v-for="(preview, idx) in extraPhotoPreviews"
                    :key="'ep-' + idx"
                    class="relative aspect-square rounded-xl overflow-hidden group"
                  >
                    <img :src="preview" class="w-full h-full object-cover" />
                    <button
                      @click="removeExtraPhoto(idx)"
                      class="absolute top-1.5 right-1.5 w-6 h-6 bg-black/60 backdrop-blur-sm rounded-lg flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity"
                    >
                      <svg
                        class="w-3 h-3"
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

                  <!-- Add photo slot -->
                  <div
                    v-if="extraPhotoPreviews.length < 5"
                    @click="$refs.extraInput.click()"
                    class="aspect-square rounded-xl border-2 border-dashed border-gray-200 hover:border-gray-400 flex flex-col items-center justify-center gap-1.5 cursor-pointer transition-colors"
                  >
                    <input
                      ref="extraInput"
                      type="file"
                      accept="image/*"
                      multiple
                      class="hidden"
                      @change="handleExtraFiles"
                    />
                    <svg
                      class="w-5 h-5 text-gray-400"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 4v16m8-8H4"
                      />
                    </svg>
                    <span
                      class="text-[10px] text-gray-400 text-center leading-tight"
                      >Tambah<br />Foto</span
                    >
                  </div>
                </div>
              </div>

              <!-- Tips card -->
              <div
                class="bg-blue-50 border border-blue-100 rounded-xl px-4 py-3.5 flex gap-3"
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
                  Gunakan foto dengan pencahayaan yang baik agar calon penawar
                  dapat melihat detail barang secara jelas. Foto dari berbagai
                  sudut meningkatkan kepercayaan pembeli.
                </p>
              </div>
            </div>
          </div>

          <!-- ── SECTION 3: Pengaturan Lelang ──────────────────────────── -->
          <div
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
            :class="{ 'ring-2 ring-black ring-offset-2': currentStep === 2 }"
          >
            <div
              class="px-6 py-5 border-b border-gray-100 flex items-center justify-between cursor-pointer"
              @click="toggleSection(2)"
            >
              <div class="flex items-center gap-3">
                <div
                  :class="[
                    'w-8 h-8 rounded-lg flex items-center justify-center text-sm shrink-0',
                    sectionDone[2]
                      ? 'bg-black text-white'
                      : 'bg-gray-100 text-gray-500',
                  ]"
                >
                  <svg
                    v-if="sectionDone[2]"
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2.5"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                  <span v-else class="font-bold">3</span>
                </div>
                <div>
                  <p class="font-semibold text-sm">Pengaturan Lelang</p>
                  <p class="text-xs text-gray-400 mt-0.5">
                    Harga, durasi, dan fitur tambahan
                  </p>
                </div>
              </div>
              <svg
                :class="[
                  'w-4 h-4 text-gray-400 transition-transform duration-200',
                  openSections[2] ? 'rotate-180' : '',
                ]"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </div>

            <div
              v-show="openSections[2]"
              class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-5"
            >
              <!-- Harga Awal -->
              <div>
                <label class="field-label"
                  >Harga Awal <span class="text-red-400">*</span></label
                >
                <div class="relative">
                  <span
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-medium"
                    >Rp</span
                  >
                  <input
                    v-model="form.startPrice"
                    type="number"
                    placeholder="1.000.000"
                    :class="[
                      'field-input pl-10',
                      errors.startPrice ? 'field-error' : '',
                    ]"
                  />
                </div>
                <p v-if="errors.startPrice" class="field-error-msg">
                  {{ errors.startPrice }}
                </p>
                <p
                  v-else-if="form.startPrice"
                  class="text-xs text-gray-400 mt-1"
                >
                  {{ formatRupiah(form.startPrice) }}
                </p>
              </div>

              <!-- Min Kenaikan -->
              <div>
                <label class="field-label"
                  >Kelipatan Minimum Penawaran
                  <span class="text-red-400">*</span></label
                >
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
                <p v-if="errors.minIncrement" class="field-error-msg">
                  {{ errors.minIncrement }}
                </p>
              </div>

              <!-- Buy Now -->
              <div>
                <label class="field-label">
                  Harga Beli Sekarang
                  <span
                    class="ml-2 text-[10px] font-normal bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full"
                    >Opsional</span
                  >
                </label>
                <div class="relative">
                  <span
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-medium"
                    >Rp</span
                  >
                  <input
                    v-model="form.buyNowPrice"
                    type="number"
                    placeholder="Kosongkan jika tidak ada"
                    class="field-input pl-10"
                  />
                </div>
                <p v-if="form.buyNowPrice" class="text-xs text-gray-400 mt-1">
                  {{ formatRupiah(form.buyNowPrice) }}
                </p>
                <p class="text-xs text-gray-400 mt-1 leading-relaxed">
                  Jika seseorang membeli pada harga ini, lelang langsung
                  berakhir.
                </p>
              </div>

              <!-- Anti Sniping (Global Config — Read Only) -->
              <div>
                <label class="field-label">Anti-Sniping</label>
                <div class="border border-gray-200 rounded-xl p-4">
                  <div class="flex items-start gap-3">
                    <div class="w-9 h-9 bg-black rounded-xl flex items-center justify-center shrink-0 mt-0.5">
                      <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                      </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-2 mb-1">
                        <p class="text-sm font-semibold text-black">Proteksi Aktif</p>
                        <span class="text-[10px] font-medium bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">Global</span>
                      </div>
                      <p class="text-xs text-gray-500 leading-relaxed">
                        Semua lelang secara otomatis dilindungi oleh fitur anti-sniping.
                        Jika ada tawaran masuk dalam <strong class="text-black">30 detik terakhir</strong>
                        sebelum lelang berakhir, waktu lelang akan diperpanjang 30 detik
                        untuk memberikan kesempatan fair bagi semua penawar.
                      </p>
                      <div class="mt-3 flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2">
                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-xs text-gray-500">Perpanjangan otomatis: <strong class="text-black">30 detik</strong></p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Jadwal Mulai -->
              <div>
                <label class="field-label"
                  >Tanggal & Jam Mulai
                  <span class="text-red-400">*</span></label
                >
                <div class="flex gap-2">
                  <input
                    v-model="form.startDate"
                    type="date"
                    :class="[
                      'field-input flex-1',
                      errors.startDate ? 'field-error' : '',
                    ]"
                    :min="todayStr"
                  />
                  <input
                    v-model="form.startTime"
                    type="time"
                    :class="[
                      'field-input w-32',
                      errors.startDate ? 'field-error' : '',
                    ]"
                  />
                </div>
                <p v-if="errors.startDate" class="field-error-msg">
                  {{ errors.startDate }}
                </p>
              </div>

              <!-- Jadwal Berakhir -->
              <div>
                <label class="field-label"
                  >Tanggal & Jam Berakhir
                  <span class="text-red-400">*</span></label
                >
                <div class="flex gap-2">
                  <input
                    v-model="form.endDate"
                    type="date"
                    :class="[
                      'field-input flex-1',
                      errors.endDate ? 'field-error' : '',
                    ]"
                    :min="form.startDate || todayStr"
                  />
                  <input
                    v-model="form.endTime"
                    type="time"
                    :class="[
                      'field-input w-32',
                      errors.endDate ? 'field-error' : '',
                    ]"
                  />
                </div>
                <p v-if="errors.endDate" class="field-error-msg">
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
                    <p class="text-xs text-gray-400">Durasi Lelang</p>
                    <p class="text-sm font-bold mt-0.5">
                      {{ auctionDuration }}
                    </p>
                  </div>
                  <div class="ml-auto">
                    <span
                      :class="[
                        'text-xs font-medium px-3 py-1.5 rounded-full',
                        auctionStartsNow
                          ? 'bg-black text-white'
                          : 'bg-amber-100 text-amber-700',
                      ]"
                    >
                      {{
                        auctionStartsNow
                          ? "⚡ Langsung Aktif"
                          : "🕐 Akan Datang"
                      }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ── SECTION 4: Persetujuan ──────────────────────────────── -->
          <div class="bg-white rounded-2xl border border-gray-100 px-6 py-5">
            <label class="flex items-start gap-4 cursor-pointer group">
              <div class="shrink-0 mt-0.5">
                <div
                  @click="form.agreed = !form.agreed"
                  :class="[
                    'w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all duration-200',
                    form.agreed
                      ? 'bg-black border-black'
                      : 'border-gray-300 group-hover:border-gray-500',
                  ]"
                >
                  <svg
                    v-if="form.agreed"
                    class="w-3 h-3 text-white"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="3"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                </div>
              </div>
              <p class="text-sm text-gray-600 leading-relaxed">
                Saya menyatakan bahwa informasi yang diberikan benar dan barang
                yang dilelang merupakan milik saya sendiri. Saya menyetujui
                <a href="#" class="text-black underline underline-offset-2"
                  >Syarat & Ketentuan</a
                >
                dan
                <a href="#" class="text-black underline underline-offset-2"
                  >Kebijakan Penjual</a
                >
                ArtBid Bali.
              </p>
            </label>
            <p v-if="errors.agreed" class="field-error-msg mt-2 ml-9">
              {{ errors.agreed }}
            </p>
          </div>

          <!-- ── ACTION BUTTONS ──────────────────────────────────────── -->
          <div class="flex flex-col sm:flex-row gap-3 pb-10">
            <button
              @click="saveDraft"
              class="sm:w-48 py-3.5 border-2 border-gray-200 text-gray-600 rounded-xl text-sm font-semibold hover:border-black hover:text-black transition-all duration-200"
            >
              Simpan Sebagai Draft
            </button>
            <button
              @click="publishAuction"
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
              {{ isSubmitting ? "Memproses..." : "Publikasikan Lelang" }}
            </button>
          </div>
        </div>
        <!-- end form sections -->

        <!-- ── SIDEBAR: Ringkasan ──────────────────────────────────── -->
        <aside
          class="hidden xl:flex flex-col gap-4 w-72 shrink-0 sticky top-24"
        >
          <!-- Preview card -->
          <div
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
          >
            <!-- Mini preview image -->
            <div class="aspect-video bg-gray-100 overflow-hidden">
              <img
                v-if="mainPhotoPreview"
                :src="mainPhotoPreview"
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
                  <p class="text-xs text-gray-400">Harga Buy Now</p>
                  <p class="text-xs font-semibold">
                    {{
                      form.buyNowPrice ? formatRupiah(form.buyNowPrice) : "—"
                    }}
                  </p>
                </div>
                <div class="flex items-center justify-between">
                  <p class="text-xs text-gray-400">Min. Kenaikan</p>
                  <p class="text-xs font-semibold">
                    {{
                      form.minIncrement ? formatRupiah(form.minIncrement) : "—"
                    }}
                  </p>
                </div>
                <div
                  class="border-t border-gray-100 pt-2.5 flex items-center justify-between"
                >
                  <p class="text-xs text-gray-400">Durasi</p>
                  <p class="text-xs font-semibold">
                    {{ auctionDuration || "—" }}
                  </p>
                </div>
              </div>

              <div class="mt-4">
                <span
                  :class="[
                    'text-xs font-medium px-3 py-1.5 rounded-full',
                    auctionStartsNow && auctionDuration
                      ? 'bg-black text-white'
                      : 'bg-amber-100 text-amber-700',
                  ]"
                >
                  {{
                    auctionDuration
                      ? auctionStartsNow
                        ? "⚡ Langsung Aktif"
                        : "🕐 Akan Datang"
                      : "— Status"
                  }}
                </span>
              </div>
            </div>
          </div>

          <!-- Completion checklist -->
          <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <p
              class="text-xs font-semibold tracking-[0.15em] uppercase text-gray-400 mb-4"
            >
              Kelengkapan
            </p>
            <div class="space-y-2.5">
              <div
                v-for="check in completionChecks"
                :key="check.label"
                class="flex items-center gap-3"
              >
                <div
                  :class="[
                    'w-4 h-4 rounded flex items-center justify-center shrink-0',
                    check.done ? 'bg-black' : 'bg-gray-100',
                  ]"
                >
                  <svg
                    v-if="check.done"
                    class="w-2.5 h-2.5 text-white"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="3"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                </div>
                <p
                  :class="[
                    'text-xs',
                    check.done ? 'text-gray-800' : 'text-gray-400',
                  ]"
                >
                  {{ check.label }}
                </p>
              </div>
            </div>
            <!-- Progress bar -->
            <div class="mt-4 bg-gray-100 rounded-full h-1.5 overflow-hidden">
              <div
                class="h-full bg-black rounded-full transition-all duration-500"
                :style="{ width: completionPct + '%' }"
              ></div>
            </div>
            <p class="text-xs text-gray-400 mt-1.5">
              {{ completionPct }}% lengkap
            </p>
          </div>

          <!-- Tips card -->
          <div class="bg-gray-50 rounded-2xl border border-gray-100 p-5">
            <p
              class="text-xs font-semibold tracking-[0.15em] uppercase text-gray-400 mb-3"
            >
              Tips Penjual
            </p>
            <ul class="space-y-2">
              <li
                v-for="tip in tips"
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

.card-lift {
  transition:
    transform 0.3s ease,
    box-shadow 0.3s ease;
}
.card-lift:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}
</style>
