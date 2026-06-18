<script setup>
import { computed, onMounted, ref } from "vue";
import {
  createCategory,
  deleteCategory,
  getCategories,
  updateCategory,
} from "../../../api/categories";

// ─── State Loading ───────────────────────────────────────────
const isLoading = ref(false);

// ─── System alerts (Tetap untuk UI mockup) ─────────────────────
const systemAlerts = [
  {
    text: "Terdapat kategori yang belum digunakan pada lelang mana pun.",
    action: "Tinjau",
    dark: false,
    icon: "M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    text: 'Kategori "Lukisan" menjadi kategori paling populer bulan ini.',
    action: "Lihat Detail",
    dark: true,
    icon: "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z",
  },
];

// ─── Categories data ─────────────────────────────────────────
const categories = ref([]);

// Mengambil data kategori dari API backend
async function fetchCategories() {
  isLoading.value = true;
  try {
    const { data } = await getCategories();
    categories.value = data;
  } catch (err) {
    console.error("Gagal mengambil data kategori:", err);
    alert("Gagal mengambil data kategori lelang dari server.");
  } finally {
    isLoading.value = false;
  }
}

// Jalankan pengambilan data saat komponen dimuat
onMounted(() => {
  fetchCategories();
});

// ─── Stats computed secara dinamis ───────────────────────────
const stats = computed(() => {
  const total = categories.value.length;
  const activeCount = categories.value.filter(
    (c) => c.totalAuctions > 0,
  ).length;
  const totalAuctions = categories.value.reduce(
    (sum, c) => sum + (c.totalAuctions || 0),
    0,
  );

  // Cari kategori paling populer (jumlah lelang terbanyak)
  let popular = "—";
  if (total > 0) {
    const sorted = [...categories.value].sort(
      (a, b) => b.totalAuctions - a.totalAuctions,
    );
    if (sorted[0] && sorted[0].totalAuctions > 0) {
      popular = sorted[0].name;
    }
  }

  return [
    {
      label: "Total Kategori",
      value: String(total),
      sub: "Jumlah seluruh kategori",
      dark: false,
      icon: "M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z",
    },
    {
      label: "Kategori Aktif",
      value: String(activeCount),
      sub: "Digunakan pada platform",
      dark: true,
      icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
    },
    {
      label: "Total Lelang",
      value: String(totalAuctions),
      sub: "Lelang terkategorisasi",
      dark: false,
      icon: "M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10",
    },
    {
      label: "Terpopuler",
      value: popular,
      sub: "Kategori lelang terbanyak",
      dark: false,
      icon: "M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z",
    },
  ];
});

// ─── Filters ─────────────────────────────────────────────────
const searchQuery = ref("");
const sortBy = ref("name_asc");
const currentPage = ref(1);
const perPage = 10;

const filteredCategories = computed(() => {
  let list = [...categories.value];

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase();
    list = list.filter((c) => c.name.toLowerCase().includes(q));
  }

  if (sortBy.value === "name_asc")
    list.sort((a, b) => a.name.localeCompare(b.name));
  else if (sortBy.value === "name_desc")
    list.sort((a, b) => b.name.localeCompare(a.name));
  else if (sortBy.value === "newest") list.sort((a, b) => b.id - a.id);
  else if (sortBy.value === "oldest") list.sort((a, b) => a.id - b.id);
  else if (sortBy.value === "most_auctions")
    list.sort((a, b) => b.totalAuctions - a.totalAuctions);

  return list;
});

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredCategories.value.length / perPage)),
);

const paginatedCategories = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredCategories.value.slice(start, start + perPage);
});

const visiblePages = computed(() => {
  const pages = [];
  const total = totalPages.value;
  const cur = currentPage.value;
  if (total <= 5) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    pages.push(1);
    if (cur > 3) pages.push("...");
    for (let i = Math.max(2, cur - 1); i <= Math.min(total - 1, cur + 1); i++)
      pages.push(i);
    if (cur < total - 2) pages.push("...");
    pages.push(total);
  }
  return pages;
});

// ─── Selection ───────────────────────────────────────────────
const selectedId = ref(null);
const selectedCategory = ref(null);

function selectCategory(cat) {
  if (!cat) return;
  if (selectedId.value === cat.id) {
    selectedId.value = null;
    selectedCategory.value = null;
  } else {
    selectedId.value = cat.id;
    selectedCategory.value = cat;
  }
}

// ─── Modal: Add / Edit ───────────────────────────────────────
const showFormModal = ref(false);
const editTarget = ref(null);
const formData = ref({ name: "", description: "", icon: "" });
const imageFile = ref(null);
const imagePreviewUrl = ref(null);

function openAddModal() {
  editTarget.value = null;
  formData.value = { name: "", description: "", icon: "" };
  imageFile.value = null;
  imagePreviewUrl.value = null;
  showFormModal.value = true;
}

function openEditModal(cat) {
  editTarget.value = cat;
  formData.value = {
    name: cat.name,
    description: cat.description,
    icon: cat.icon,
  };
  imageFile.value = null;
  imagePreviewUrl.value = cat.image || null;
  showFormModal.value = true;
}

function handleImageChange(e) {
  const file = e.target.files[0];
  if (file) {
    imageFile.value = file;
    imagePreviewUrl.value = URL.createObjectURL(file);
  }
}

async function handleFormSubmit() {
  if (!formData.value.name.trim()) return;

  const fd = new FormData();
  fd.append("name", formData.value.name);
  if (formData.value.description) {
    fd.append("description", formData.value.description);
  }
  if (formData.value.icon) {
    fd.append("icon", formData.value.icon);
  }
  if (imageFile.value) {
    fd.append("image", imageFile.value);
  }

  try {
    if (editTarget.value) {
      await updateCategory(editTarget.value.id, fd);
    } else {
      await createCategory(fd);
    }
    showFormModal.value = false;
    await fetchCategories();

    // Perbarui panel detail jika ada kategori terpilih yang baru diedit
    if (
      editTarget.value &&
      selectedCategory.value?.id === editTarget.value.id
    ) {
      const updated = categories.value.find(
        (c) => c.id === editTarget.value.id,
      );
      selectedCategory.value = updated ? { ...updated } : null;
    }
  } catch (err) {
    console.error("Gagal menyimpan kategori:", err);
    alert(err.response?.data?.message || "Gagal menyimpan data kategori.");
  }
}

// ─── Modal: Delete ───────────────────────────────────────────
const deleteTarget = ref(null);

function confirmDelete(cat) {
  deleteTarget.value = cat;
}

async function handleDelete() {
  if (!deleteTarget.value) return;

  try {
    await deleteCategory(deleteTarget.value.id);
    if (selectedCategory.value?.id === deleteTarget.value.id) {
      selectedCategory.value = null;
      selectedId.value = null;
    }
    deleteTarget.value = null;
    await fetchCategories();
  } catch (err) {
    console.error("Gagal menghapus kategori:", err);
    alert(err.response?.data?.message || "Gagal menghapus kategori.");
  }
}

// ─── Top categories (sidebar) ────────────────────────────────
const topCategories = computed(() =>
  [...categories.value]
    .sort((a, b) => b.totalAuctions - a.totalAuctions)
    .slice(0, 5),
);

// ─── Recent activity (static) ────────────────────────────────
const recentActivity = [
  {
    text: 'Administrator menambahkan kategori "Ukiran Kayu".',
    time: "1 hari lalu",
    dark: false,
    icon: "M12 4v16m8-8H4",
  },
  {
    text: 'Administrator memperbarui kategori "Barang Antik".',
    time: "3 hari lalu",
    dark: true,
    icon: "M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z",
  },
  {
    text: 'Kategori "Lukisan" digunakan pada 3 lelang baru hari ini.',
    time: "Hari ini",
    dark: false,
    icon: "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z",
  },
];

// ─── Helpers ─────────────────────────────────────────────────
function formatRp(val) {
  return "Rp " + val.toLocaleString("id-ID");
}

function auctionStatusStyle(status) {
  return status === "active"
    ? { class: "bg-gray-100 text-gray-700", dot: "bg-gray-700", label: "Aktif" }
    : { class: "bg-black text-white", dot: "bg-white", label: "Selesai" };
}
</script>
<template>
  <div class="flex-1 px-8 py-8 space-y-6 min-h-screen bg-gray-50 font-sans">
    <!-- ═══════════════════ HEADER ═══════════════════ -->
    <div class="flex justify-between items-center">
      <div>
        <span
          class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400"
          >Admin Panel</span
        >
        <h1 class="text-3xl font-bold text-black mt-1.5 tracking-tight">
          Manajemen Kategori
        </h1>
        <p class="text-gray-500 text-sm mt-2 max-w-2xl leading-relaxed">
          Kelola kategori barang seni yang digunakan dalam sistem untuk membantu
          proses pengelompokan dan pencarian barang lelang.
        </p>
      </div>
      <button
        @click="openAddModal"
        class="flex items-center gap-2 px-5 py-2.5 bg-black text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors"
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
            d="M12 4v16m8-8H4"
          />
        </svg>
        Tambah Kategori
      </button>
    </div>

    <!-- ═══════════════════ SYSTEM ALERTS ═══════════════════ -->
    <div class="space-y-2">
      <div
        v-for="alert in systemAlerts"
        :key="alert.text"
        class="flex items-center gap-3 bg-white border border-gray-200 rounded-xl px-4 py-3"
      >
        <div
          :class="[
            'w-7 h-7 rounded-lg flex items-center justify-center shrink-0',
            alert.dark ? 'bg-black' : 'bg-gray-100',
          ]"
        >
          <svg
            class="w-3.5 h-3.5"
            :class="alert.dark ? 'text-white' : 'text-gray-600'"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              :d="alert.icon"
            />
          </svg>
        </div>
        <p class="text-sm text-gray-700 flex-1">{{ alert.text }}</p>
        <button
          class="text-xs text-gray-500 hover:text-black font-medium transition-colors shrink-0 whitespace-nowrap"
        >
          {{ alert.action }}
        </button>
      </div>
    </div>

    <!-- ═══════════════════ STATS ═══════════════════ -->
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
      <div
        v-for="stat in stats"
        :key="stat.label"
        :class="[
          'rounded-2xl px-6 py-6 border card-lift',
          stat.dark ? 'bg-black border-black' : 'bg-white border-gray-100',
        ]"
      >
        <div class="mb-3">
          <div
            :class="[
              'w-9 h-9 rounded-xl flex items-center justify-center',
              stat.dark ? 'bg-white/15' : 'bg-gray-100',
            ]"
          >
            <svg
              class="w-4 h-4"
              :class="stat.dark ? 'text-white' : 'text-black'"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                :d="stat.icon"
              />
            </svg>
          </div>
        </div>
        <p
          class="text-2xl font-bold"
          :class="stat.dark ? 'text-white' : 'text-black'"
        >
          {{ stat.value }}
        </p>
        <p
          class="text-sm font-medium mt-0.5"
          :class="stat.dark ? 'text-white/70' : 'text-gray-700'"
        >
          {{ stat.label }}
        </p>
        <p
          class="text-xs mt-0.5"
          :class="stat.dark ? 'text-white/40' : 'text-gray-400'"
        >
          {{ stat.sub }}
        </p>
      </div>
    </div>

    <!-- ═══════════════════ FILTER ═══════════════════ -->
    <div class="bg-white rounded-2xl border border-gray-100 p-5">
      <div class="flex flex-col xl:flex-row gap-3">
        <!-- Search -->
        <div class="relative flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari kategori..."
            class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm text-black placeholder-gray-400 outline-none focus:border-black transition-colors bg-gray-50"
            @input="currentPage = 1"
          />
          <svg
            class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
        </div>
        <!-- Sort -->
        <div class="relative">
          <select
            v-model="sortBy"
            class="appearance-none border border-gray-200 rounded-xl pl-3 pr-8 py-2.5 text-sm text-gray-600 outline-none focus:border-black transition-colors bg-gray-50 cursor-pointer"
          >
            <option value="name_asc">Nama A–Z</option>
            <option value="name_desc">Nama Z–A</option>
            <option value="newest">Terbaru</option>
            <option value="oldest">Terlama</option>
            <option value="most_auctions">Jumlah Lelang Terbanyak</option>
          </select>
          <svg
            class="w-3.5 h-3.5 text-gray-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"
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
      </div>
    </div>

    <!-- ═══════════════════ MAIN CONTENT ═══════════════════ -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- ── TABLE ── -->
      <div class="xl:col-span-2 space-y-4">
        <div
          class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
        >
          <!-- Table header -->
          <div
            class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"
          >
            <p class="text-sm font-semibold text-black">
              Menampilkan {{ paginatedCategories.length }} dari
              {{ filteredCategories.length }} kategori
            </p>
          </div>

          <!-- Empty state -->
          <div
            v-if="paginatedCategories.length === 0"
            class="py-20 text-center"
          >
            <div
              class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4"
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
                  d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
                />
              </svg>
            </div>
            <p class="font-medium text-gray-700 text-sm mb-1">
              Tidak ada kategori ditemukan
            </p>
            <p class="text-gray-400 text-xs">Coba ubah kata kunci pencarian.</p>
          </div>

          <!-- Table -->
          <div v-else class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-gray-100">
                  <th
                    class="text-left px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                  >
                    Nama Kategori
                  </th>
                  <th
                    class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell"
                  >
                    Deskripsi
                  </th>
                  <th
                    class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell"
                  >
                    Lelang
                  </th>
                  <th
                    class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden xl:table-cell"
                  >
                    Dibuat Pada
                  </th>
                  <th class="px-4 py-3"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <tr
                  v-for="cat in paginatedCategories"
                  :key="cat.id"
                  class="hover:bg-gray-50 transition-colors cursor-pointer group"
                  :class="selectedId === cat.id ? 'bg-gray-50' : ''"
                  @click="selectCategory(cat)"
                >
                  <!-- Nama -->
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-9 h-9 bg-gray-100 rounded-xl flex items-center justify-center shrink-0"
                      >
                        <svg
                          class="w-4 h-4 text-gray-700"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            :d="cat.icon"
                          />
                        </svg>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-black">
                          {{ cat.name }}
                        </p>
                        <p
                          v-if="cat.totalAuctions === 0"
                          class="text-xs text-gray-400 mt-0.5"
                        >
                          Belum ada lelang
                        </p>
                      </div>
                    </div>
                  </td>
                  <!-- Deskripsi -->
                  <td class="px-4 py-4 hidden md:table-cell">
                    <p class="text-sm text-gray-500 truncate max-w-[200px]">
                      {{ cat.description }}
                    </p>
                  </td>
                  <!-- Lelang -->
                  <td class="px-4 py-4 text-center hidden lg:table-cell">
                    <p class="text-sm font-semibold text-black">
                      {{ cat.totalAuctions }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">lelang</p>
                  </td>
                  <!-- Dibuat -->
                  <td class="px-4 py-4 hidden xl:table-cell">
                    <p class="text-xs text-gray-500 whitespace-nowrap">
                      {{ cat.createdAt }}
                    </p>
                  </td>
                  <!-- Aksi -->
                  <td class="px-4 py-4" @click.stop>
                    <div
                      class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
                    >
                      <button
                        @click="selectCategory(cat)"
                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:border-black hover:text-black transition-all"
                        title="Lihat Detail"
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
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                          />
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                          />
                        </svg>
                      </button>
                      <button
                        @click="openEditModal(cat)"
                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:border-black hover:text-black transition-all"
                        title="Edit"
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
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                          />
                        </svg>
                      </button>
                      <button
                        @click="confirmDelete(cat)"
                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:border-red-300 hover:text-red-500 transition-all"
                        title="Hapus"
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
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                          />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div
            v-if="filteredCategories.length > 0"
            class="px-6 py-4 border-t border-gray-100 flex items-center justify-between gap-4"
          >
            <p class="text-xs text-gray-400">
              Menampilkan {{ (currentPage - 1) * perPage + 1 }}–{{
                Math.min(currentPage * perPage, filteredCategories.length)
              }}
              dari {{ filteredCategories.length }} kategori
            </p>
            <div class="flex items-center gap-1">
              <button
                @click="currentPage--"
                :disabled="currentPage === 1"
                class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg text-gray-500 hover:border-black hover:text-black transition-all disabled:opacity-40 disabled:cursor-not-allowed"
              >
                Sebelumnya
              </button>
              <button
                v-for="p in visiblePages"
                :key="p"
                @click="p !== '...' && (currentPage = p)"
                :class="[
                  'w-8 h-8 text-xs rounded-lg border transition-all',
                  p === '...'
                    ? 'border-transparent text-gray-400 cursor-default'
                    : currentPage === p
                      ? 'bg-black border-black text-white'
                      : 'border-gray-200 text-gray-500 hover:border-black hover:text-black',
                ]"
              >
                {{ p }}
              </button>
              <button
                @click="currentPage++"
                :disabled="currentPage === totalPages"
                class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg text-gray-500 hover:border-black hover:text-black transition-all disabled:opacity-40 disabled:cursor-not-allowed"
              >
                Berikutnya
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ── RIGHT SIDEBAR ── -->
      <div class="space-y-4">
        <!-- Category detail panel -->
        <transition name="slide-fade">
          <div
            v-if="selectedCategory"
            class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
          >
            <!-- Header -->
            <div class="p-5 border-b border-gray-100">
              <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                  <div
                    class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center shrink-0"
                  >
                    <svg
                      class="w-5 h-5 text-gray-700"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        :d="selectedCategory.icon"
                      />
                    </svg>
                  </div>
                  <div>
                    <p class="font-bold text-sm text-black">
                      {{ selectedCategory.name }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">
                      {{ selectedCategory.description }}
                    </p>
                  </div>
                </div>
                <button
                  @click="
                    selectedCategory = null;
                    selectedId = null;
                  "
                  class="w-7 h-7 flex items-center justify-center rounded-lg border border-gray-200 text-gray-400 hover:border-black hover:text-black transition-all shrink-0"
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

              <div class="space-y-1.5">
                <div class="flex items-center gap-2.5 text-xs text-gray-500">
                  <svg
                    class="w-3.5 h-3.5 text-gray-400 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                  Dibuat {{ selectedCategory.createdAt }}
                </div>
                <div class="flex items-center gap-2.5 text-xs text-gray-500">
                  <svg
                    class="w-3.5 h-3.5 text-gray-400 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                    />
                  </svg>
                  Diperbarui {{ selectedCategory.updatedAt }}
                </div>
              </div>
            </div>

            <!-- Stats -->
            <div class="p-5 border-b border-gray-100">
              <p
                class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-3"
              >
                Statistik Penggunaan
              </p>
              <div class="grid grid-cols-3 gap-3">
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                  <p class="text-lg font-bold text-black">
                    {{ selectedCategory.totalAuctions }}
                  </p>
                  <p class="text-xs text-gray-400 mt-0.5">Lelang</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                  <p class="text-lg font-bold text-black">
                    {{ selectedCategory.totalBids.toLocaleString("id-ID") }}
                  </p>
                  <p class="text-xs text-gray-400 mt-0.5">Bid</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                  <p class="text-lg font-bold text-black">
                    {{ selectedCategory.totalSellers }}
                  </p>
                  <p class="text-xs text-gray-400 mt-0.5">Penjual</p>
                </div>
              </div>
            </div>

            <!-- Recent items -->
            <div class="p-5 border-b border-gray-100">
              <p
                class="text-xs text-gray-400 uppercase tracking-widest font-semibold mb-3"
              >
                Barang Terbaru
              </p>
              <div
                v-if="selectedCategory.recentItems.length === 0"
                class="py-4 text-center"
              >
                <p class="text-xs text-gray-400">
                  Belum ada barang dalam kategori ini.
                </p>
              </div>
              <div class="space-y-2.5">
                <div
                  v-for="(item, i) in selectedCategory.recentItems"
                  :key="i"
                  class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100"
                >
                  <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium text-black truncate">
                      {{ item.name }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                      {{ item.seller }} · {{ formatRp(item.price) }}
                    </p>
                  </div>
                  <span
                    :class="[
                      'ml-2 shrink-0 text-xs px-2 py-0.5 rounded-full font-medium inline-flex items-center gap-1',
                      auctionStatusStyle(item.status).class,
                    ]"
                  >
                    <span
                      class="w-1.5 h-1.5 rounded-full"
                      :class="auctionStatusStyle(item.status).dot"
                    ></span>
                    {{ auctionStatusStyle(item.status).label }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="p-5 flex flex-col gap-2">
              <button
                @click="openEditModal(selectedCategory)"
                class="w-full py-2.5 bg-black text-white rounded-xl text-xs font-medium hover:bg-gray-800 transition-colors"
              >
                Edit Kategori
              </button>
              <button
                @click="confirmDelete(selectedCategory)"
                class="w-full py-2.5 border border-gray-200 text-gray-600 rounded-xl text-xs font-medium hover:border-red-300 hover:text-red-500 transition-all duration-300"
              >
                Hapus Kategori
              </button>
            </div>
          </div>
        </transition>

        <!-- Default panels -->
        <template v-if="!selectedCategory">
          <!-- Top categories -->
          <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-sm font-semibold text-black">
                Kategori Terpopuler
              </h2>
              <button
                class="text-xs text-gray-400 hover:text-black transition-colors font-medium"
              >
                Lihat Semua
              </button>
            </div>
            <div class="space-y-3">
              <div
                v-for="(cat, i) in topCategories"
                :key="cat.id"
                class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 rounded-xl p-2 -mx-2 transition-colors"
                :class="
                  i < topCategories.length - 1
                    ? 'pb-3 border-b border-gray-50'
                    : ''
                "
                @click="selectCategory(categories.find((c) => c.id === cat.id))"
              >
                <span class="text-xs font-bold text-gray-300 w-4 shrink-0">{{
                  String(i + 1).padStart(2, "0")
                }}</span>
                <div
                  class="w-8 h-8 bg-gray-100 rounded-xl flex items-center justify-center shrink-0"
                >
                  <svg
                    class="w-3.5 h-3.5 text-gray-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      :d="cat.icon"
                    />
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-xs font-semibold text-black truncate">
                    {{ cat.name }}
                  </p>
                  <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-xs text-gray-400"
                      >{{ cat.totalAuctions }} lelang</span
                    >
                    <span class="text-xs text-gray-300">·</span>
                    <span class="text-xs text-gray-400"
                      >{{ cat.totalBids.toLocaleString("id-ID") }} bid</span
                    >
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent activity -->
          <div class="bg-white rounded-2xl border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-sm font-semibold text-black">
                Aktivitas Terbaru
              </h2>
              <div class="flex items-center gap-1.5">
                <span
                  class="live-dot w-1.5 h-1.5 rounded-full bg-black inline-block"
                ></span>
                <span class="text-xs text-gray-400">Live</span>
              </div>
            </div>
            <div class="space-y-3">
              <div
                v-for="(act, i) in recentActivity"
                :key="i"
                class="flex items-start gap-3"
                :class="
                  i < recentActivity.length - 1
                    ? 'pb-3 border-b border-gray-50'
                    : ''
                "
              >
                <div
                  :class="[
                    'w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5',
                    act.dark ? 'bg-black' : 'bg-gray-100',
                  ]"
                >
                  <svg
                    class="w-3 h-3"
                    :class="act.dark ? 'text-white' : 'text-gray-600'"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      :d="act.icon"
                    />
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-gray-700 leading-relaxed">
                    {{ act.text }}
                  </p>
                  <p class="text-xs text-gray-400 mt-0.5">{{ act.time }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Summary card -->
          <div class="bg-black rounded-2xl p-5">
            <p class="text-white/50 text-xs uppercase tracking-widest mb-3">
              Ringkasan Kategori
            </p>
            <p class="text-white font-bold text-3xl">12</p>
            <p class="text-white/40 text-xs mt-1.5">Total kategori tersedia</p>
            <div
              class="mt-4 pt-4 border-t border-white/10 grid grid-cols-2 gap-3"
            >
              <div>
                <p class="text-white/40 text-xs mb-1">Total lelang</p>
                <p class="text-white text-sm font-semibold">325</p>
              </div>
              <div>
                <p class="text-white/40 text-xs mb-1">Terpopuler</p>
                <p class="text-white text-sm font-semibold">Lukisan</p>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- ═══════════════════ FORM MODAL (Add / Edit) ═══════════════════ -->
    <transition name="fade-modal">
      <div
        v-if="showFormModal"
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        @click.self="showFormModal = false"
      >
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div
          class="relative bg-white rounded-2xl p-7 max-w-md w-full shadow-xl"
        >
          <div class="flex items-center justify-between mb-6">
            <div>
              <h3 class="font-bold text-lg text-black">
                {{ editTarget ? "Edit Kategori" : "Tambah Kategori" }}
              </h3>
              <p class="text-xs text-gray-400 mt-0.5">
                {{
                  editTarget
                    ? "Perbarui informasi kategori yang sudah ada."
                    : "Tambahkan kategori baru ke dalam sistem."
                }}
              </p>
            </div>
            <button
              @click="showFormModal = false"
              class="w-8 h-8 flex items-center justify-center rounded-xl border border-gray-200 text-gray-400 hover:border-black hover:text-black transition-all"
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

          <div class="space-y-4">
            <!-- Nama -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                >Nama Kategori <span class="text-red-400">*</span></label
              >
              <input
                v-model="formData.name"
                type="text"
                placeholder="Contoh: Lukisan"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-black placeholder-gray-400 outline-none focus:border-black transition-colors bg-gray-50"
              />
            </div>
            <!-- Deskripsi -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1.5"
                >Deskripsi Kategori</label
              >
              <textarea
                v-model="formData.description"
                rows="3"
                placeholder="Contoh: Kategori untuk berbagai jenis karya seni lukis tradisional maupun modern."
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-black placeholder-gray-400 outline-none focus:border-black transition-colors bg-gray-50 resize-none"
              ></textarea>
            </div>
            <!-- Ikon (opsional) -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                SVG Path Ikon Kategori
                <span class="text-gray-400 font-normal ml-1">(Opsional)</span>
              </label>
              <input
                v-model="formData.icon"
                type="text"
                placeholder="Contoh: M4 16l4.586-4.586a2 2..."
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-black placeholder-gray-400 outline-none focus:border-black transition-colors bg-gray-50"
              />
            </div>
            <!-- Gambar (opsional) -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                Gambar Kategori
                <span class="text-gray-400 font-normal ml-1">(Opsional)</span>
              </label>
              <div
                @click="$refs.imageInput.click()"
                class="border border-dashed border-gray-200 rounded-xl px-4 py-5 text-center bg-gray-50 hover:border-gray-400 transition-colors cursor-pointer"
              >
                <input
                  ref="imageInput"
                  type="file"
                  accept="image/*"
                  class="hidden"
                  @change="handleImageChange"
                />
                <div v-if="imagePreviewUrl" class="relative w-full h-32 rounded-xl overflow-hidden mb-2">
                  <img :src="imagePreviewUrl" class="w-full h-full object-cover" />
                  <div
                    @click.stop="imagePreviewUrl = null; imageFile = null"
                    class="absolute top-2 right-2 w-6 h-6 bg-black/60 text-white rounded-lg flex items-center justify-center hover:bg-black transition-colors"
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
                  </div>
                </div>
                <div v-else>
                  <svg
                    class="w-6 h-6 text-gray-300 mx-auto mb-2"
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
                  <p class="text-xs text-gray-400">Unggah gambar thumbnail</p>
                </div>
              </div>
            </div>
          </div>

          <div class="flex gap-3 mt-6">
            <button
              @click="showFormModal = false"
              class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors"
            >
              Batal
            </button>
            <button
              @click="handleFormSubmit"
              :disabled="!formData.name.trim()"
              class="flex-1 py-2.5 bg-black text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
            >
              Simpan
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ═══════════════════ DELETE CONFIRM MODAL ═══════════════════ -->
    <transition name="fade-modal">
      <div
        v-if="deleteTarget"
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        @click.self="deleteTarget = null"
      >
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div
          class="relative bg-white rounded-2xl p-7 max-w-sm w-full shadow-xl"
        >
          <div
            class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mb-4"
          >
            <svg
              class="w-6 h-6 text-gray-700"
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

          <!-- Validasi: kategori masih dipakai -->
          <template v-if="deleteTarget.totalAuctions > 0">
            <h3 class="font-bold text-lg text-black mb-2">
              Tidak Dapat Dihapus
            </h3>
            <p class="text-gray-500 text-sm leading-relaxed mb-6">
              Kategori
              <strong class="text-black">{{ deleteTarget.name }}</strong> tidak
              dapat dihapus karena masih digunakan oleh
              <strong class="text-black"
                >{{ deleteTarget.totalAuctions }} data lelang</strong
              >.
            </p>
            <button
              @click="deleteTarget = null"
              class="w-full py-2.5 bg-black text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors"
            >
              Mengerti
            </button>
          </template>

          <!-- Konfirmasi hapus -->
          <template v-else>
            <h3 class="font-bold text-lg text-black mb-2">Hapus Kategori?</h3>
            <p class="text-gray-500 text-sm leading-relaxed mb-6">
              Apakah Anda yakin ingin menghapus kategori
              <strong class="text-black">{{ deleteTarget.name }}</strong
              >? Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="flex gap-3">
              <button
                @click="deleteTarget = null"
                class="flex-1 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors"
              >
                Batal
              </button>
              <button
                @click="handleDelete"
                class="flex-1 py-2.5 bg-black text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors"
              >
                Hapus
              </button>
            </div>
          </template>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
.font-sans {
  font-family: "DM Sans", sans-serif;
}

.card-lift {
  transition:
    transform 0.25s ease,
    box-shadow 0.25s ease;
}
.card-lift:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
}

@keyframes pulseDot {
  0%,
  100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.5);
    opacity: 0.5;
  }
}
.live-dot {
  animation: pulseDot 1.4s ease-in-out infinite;
}

.slide-fade-enter-active {
  transition:
    opacity 0.25s ease,
    transform 0.25s ease;
}
.slide-fade-leave-active {
  transition: opacity 0.2s ease;
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateY(10px);
}
.slide-fade-leave-to {
  opacity: 0;
}

.fade-modal-enter-active {
  transition: opacity 0.25s ease;
}
.fade-modal-leave-active {
  transition: opacity 0.2s ease;
}
.fade-modal-enter-from,
.fade-modal-leave-to {
  opacity: 0;
}
</style>
