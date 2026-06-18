<template>
  <header
    class="sticky top-0 z-30 bg-white border-b border-gray-100 px-8 py-3.5 flex items-center justify-between gap-4"
  >
    <!-- Search -->
    <div class="relative flex-1 max-w-md">
      <input
        v-model="searchQuery"
        type="text"
        :placeholder="
          route.path === '/admin/dashboard'
            ? 'Cari laporan, pengguna, atau lelang...'
            : 'Cari di halaman ini...'
        "
        class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2 text-sm text-black placeholder-gray-400 outline-none focus:border-black transition-colors bg-gray-50"
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

    <div class="flex items-center gap-2">
      <!-- Notification bell -->
      <div class="relative" ref="notifRef">
        <button
          @click="notifOpen = !notifOpen"
          class="relative w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center text-gray-500 hover:border-black hover:text-black transition-colors"
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
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
            />
          </svg>
          <span
            class="absolute top-1.5 right-1.5 w-2 h-2 bg-black rounded-full border-2 border-white"
          ></span>
        </button>

        <transition name="dropdown">
          <div
            v-if="notifOpen"
            class="absolute right-0 top-full mt-2 w-80 bg-white rounded-2xl border border-gray-100 shadow-xl overflow-hidden z-50"
          >
            <div
              class="px-5 py-4 border-b border-gray-100 flex items-center justify-between"
            >
              <p class="font-semibold text-sm text-black">Notifikasi Sistem</p>
              <span
                class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full"
              >
                {{ systemNotifs.length }} baru
              </span>
            </div>
            <div class="divide-y divide-gray-50">
              <div
                v-for="(n, i) in systemNotifs"
                :key="i"
                class="px-5 py-3.5 hover:bg-gray-50 transition-colors cursor-pointer"
              >
                <div class="flex items-start gap-3">
                  <div
                    :class="[
                      'w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5',
                      n.dark ? 'bg-black' : 'bg-gray-100',
                    ]"
                  >
                    <svg
                      class="w-3.5 h-3.5"
                      :class="n.dark ? 'text-white' : 'text-gray-600'"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        :d="n.icon"
                      />
                    </svg>
                  </div>
                  <div>
                    <p class="text-xs text-gray-700 leading-relaxed">
                      {{ n.text }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ n.time }}</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="px-5 py-3 border-t border-gray-100 text-center">
              <button
                class="text-xs text-gray-500 hover:text-black transition-colors font-medium"
              >
                Lihat semua notifikasi
              </button>
            </div>
          </div>
        </transition>
      </div>

      <div class="w-px h-5 bg-gray-200"></div>

      <!-- Admin profile -->
      <div class="relative" ref="profileRef">
        <button
          @click="profileOpen = !profileOpen"
          class="flex items-center gap-2.5 pl-1 pr-3 py-1 rounded-xl hover:bg-gray-50 transition-colors"
        >
          <div
            class="w-7 h-7 rounded-lg bg-black flex items-center justify-center text-white text-xs font-bold shrink-0"
          >
            {{ adminInitials }}
          </div>
          <div class="text-left hidden sm:block">
            <p class="text-xs font-semibold text-gray-800 leading-none">
              {{ adminName }}
            </p>
            <p class="text-xs text-gray-400 mt-0.5">Administrator</p>
          </div>
          <svg
            class="w-3 h-3 text-gray-400 transition-transform duration-200"
            :class="profileOpen ? 'rotate-180' : ''"
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
        </button>

        <transition name="dropdown">
          <div
            v-if="profileOpen"
            class="absolute right-0 top-full mt-2 w-48 bg-white rounded-xl border border-gray-100 shadow-lg overflow-hidden py-1.5 z-50"
          >
            <router-link
              to="/admin/settings"
              @click="profileOpen = false"
              class="flex items-center gap-3 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-black transition-colors"
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
                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
              </svg>
              Pengaturan
            </router-link>
            <div class="border-t border-gray-100 mt-1 pt-1">
              <button
                @click="handleLogout"
                class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-500 hover:bg-gray-50 hover:text-black transition-colors"
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
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                  />
                </svg>
                Logout
              </button>
            </div>
          </div>
        </transition>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";

const router = useRouter();
const route = useRoute();

const searchQuery = ref("");
const notifOpen = ref(false);
const profileOpen = ref(false);
const notifRef = ref(null);
const profileRef = ref(null);

const adminName = ref("Admin Bali");
const adminInitials = computed(() =>
  adminName.value
    .split(" ")
    .map((w) => w[0])
    .slice(0, 2)
    .join("")
    .toUpperCase(),
);

const systemNotifs = [
  {
    text: "Terdapat 3 laporan pengguna yang belum ditinjau.",
    time: "5 menit lalu",
    dark: false,
    icon: "M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
  },
  {
    text: "Terdapat 2 lelang yang menunggu verifikasi.",
    time: "12 menit lalu",
    dark: true,
    icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
  },
  {
    text: "Sistem realtime broadcasting berjalan normal.",
    time: "1 jam lalu",
    dark: false,
    icon: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
  },
];

function handleLogout() {
  router.push("/admin/login");
}

function handleClickOutside(e) {
  if (notifRef.value && !notifRef.value.contains(e.target))
    notifOpen.value = false;
  if (profileRef.value && !profileRef.value.contains(e.target))
    profileOpen.value = false;
}

onMounted(() => document.addEventListener("click", handleClickOutside));
onUnmounted(() => document.removeEventListener("click", handleClickOutside));
</script>

<style scoped>
.dropdown-enter-active {
  transition:
    opacity 0.15s ease,
    transform 0.15s ease;
}
.dropdown-leave-active {
  transition:
    opacity 0.1s ease,
    transform 0.1s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(6px);
}
</style>
