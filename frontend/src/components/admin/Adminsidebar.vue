<script setup>
import { ref, inject } from "vue";
import { useRouter } from "vue-router";
import { Icon } from "@iconify/vue";

const router = useRouter();

const collapsed = inject("sidebarCollapsed", ref(false));
const navGroups = [
  {
    label: "Utama",
    items: [
      {
        label: "Dashboard",
        to: "/admin/dashboard",
        exact: true,
        icon: "M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6",
      },
    ],
  },
  {
    label: "Manajemen",
    items: [
      {
        label: "Manajemen Lelang",
        to: "/admin/auctions",
        icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
        badge: "78",
      },
      {
        label: "Manajemen Pengguna",
        to: "/admin/users",
        icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z",
        badge: "1.245",
      },
      {
        label: "Manajemen Kategori",
        to: "/admin/categories",
        icon: "M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z",
      },
    ],
  },
  {
    label: "Sistem",
    items: [
      {
        label: "Laporan",
        to: "/admin/reports",
        icon: "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z",
      },
      {
        label: "Pengaturan",
        to: "/admin/settings",
        icon: "M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z",
      },
    ],
  },
];

function handleLogout() {
  // TODO: panggil auth store logout
  router.push("/login");
}

function handleBerandaUser() {
  router.push("/");
}
</script>

<template>
  <aside
    :class="[
      'fixed top-0 left-0 h-full z-40 flex flex-col bg-white border-r border-gray-100 transition-all duration-300',
      collapsed ? 'w-16' : 'w-60',
    ]"
  >
    <!-- Logo -->
    <div
      :class="[
        'flex items-center border-b border-gray-100 shrink-0',
        collapsed ? 'justify-center px-0 py-5' : 'px-6 py-5 gap-3',
      ]"
    >
      <div
        class="w-8 h-8 bg-black rounded-lg flex items-center justify-center shrink-0"
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
            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"
          />
        </svg>
      </div>
      <transition name="label-fade">
        <div v-if="!collapsed">
          <p class="text-sm font-bold text-black tracking-tight leading-none">
            ArtBid<span class="font-light">Bali</span>
          </p>
          <p class="text-xs text-gray-400 mt-0.5">Admin Panel</p>
        </div>
      </transition>
    </div>

    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto py-4 px-2">
      <div v-for="group in navGroups" :key="group.label" class="mb-4">
        <transition name="label-fade">
          <p
            v-if="!collapsed"
            class="text-xs font-semibold text-gray-400 uppercase tracking-widest px-3 mb-1.5"
          >
            {{ group.label }}
          </p>
        </transition>
        <ul class="space-y-0.5">
          <li v-for="item in group.items" :key="item.to">
            <router-link
              :to="item.to"
              :title="collapsed ? item.label : ''"
              :class="[
                'flex items-center gap-3 rounded-xl text-sm transition-all duration-200 group relative',
                collapsed ? 'justify-center px-0 py-3' : 'px-3 py-2.5',
              ]"
              active-class="bg-black text-white"
              :exact="item.exact"
              custom
              v-slot="{ isActive, navigate }"
            >
              <button
                @click="navigate"
                :class="[
                  'w-full flex items-center gap-3 rounded-xl text-sm transition-all duration-200',
                  collapsed ? 'justify-center py-2.5' : 'px-3 py-2.5',
                  isActive
                    ? 'bg-black text-white'
                    : 'text-gray-500 hover:bg-gray-100 hover:text-black',
                ]"
              >
                <svg
                  class="w-4 h-4 shrink-0"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    :d="item.icon"
                  />
                </svg>
                <transition name="label-fade">
                  <span v-if="!collapsed" class="flex-1 text-left truncate">{{
                    item.label
                  }}</span>
                </transition>
                <transition name="label-fade">
                  <span
                    v-if="!collapsed && item.badge"
                    :class="[
                      'text-xs px-1.5 py-0.5 rounded-full font-medium',
                      isActive
                        ? 'bg-white/20 text-white'
                        : 'bg-gray-100 text-gray-500',
                    ]"
                  >
                    {{ item.badge }}
                  </span>
                </transition>
              </button>

              <!-- Collapsed tooltip -->
              <div
                v-if="collapsed"
                class="absolute left-full ml-2 px-2.5 py-1.5 bg-gray-900 text-white text-xs rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity duration-200 z-50"
              >
                {{ item.label }}
              </div>
            </router-link>
          </li>
        </ul>
      </div>
    </nav>

    <!-- Bottom: collapse toggle + logout -->
    <div class="shrink-0 border-t border-gray-100 p-2 space-y-0.5">
      <button
        @click="handleBerandaUser"
        :class="[
          'w-full flex items-center gap-3 rounded-xl text-sm text-gray-500 hover:bg-gray-100 hover:text-black transition-all duration-200',
          collapsed ? 'justify-center py-2.5' : 'px-3 py-2.5',
        ]"
        :title="collapsed ? 'Beranda User' : ''"
      >
        <Icon icon="mdi:home-outline" class="w-4 h-4 shrink-0" />
        <transition name="label-fade">
          <span v-if="!collapsed">Beranda User</span>
        </transition>
      </button>
      <!-- Logout -->
      <button
        @click="handleLogout"
        :class="[
          'w-full flex items-center gap-3 rounded-xl text-sm text-gray-500 hover:bg-gray-100 hover:text-black transition-all duration-200',
          collapsed ? 'justify-center py-2.5' : 'px-3 py-2.5',
        ]"
        :title="collapsed ? 'Logout' : ''"
      >
        <svg
          class="w-4 h-4 shrink-0"
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
        <transition name="label-fade">
          <span v-if="!collapsed">Logout</span>
        </transition>
      </button>

      <!-- Collapse toggle -->
      <button
        @click="collapsed = !collapsed"
        :class="[
          'w-full flex items-center gap-3 rounded-xl text-sm text-gray-400 hover:bg-gray-100 hover:text-black transition-all duration-200',
          collapsed ? 'justify-center py-2.5' : 'px-3 py-2.5',
        ]"
      >
        <svg
          class="w-4 h-4 shrink-0 transition-transform duration-300"
          :class="collapsed ? 'rotate-180' : ''"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M11 19l-7-7 7-7m8 14l-7-7 7-7"
          />
        </svg>
        <transition name="label-fade">
          <span v-if="!collapsed" class="text-xs">Ciutkan Sidebar</span>
        </transition>
      </button>
    </div>
  </aside>
</template>

<style scoped>
.font-sans {
  font-family: "DM Sans", sans-serif;
}

.label-fade-enter-active {
  transition:
    opacity 0.2s ease,
    transform 0.2s ease;
}
.label-fade-leave-active {
  transition: opacity 0.1s ease;
}
.label-fade-enter-from {
  opacity: 0;
  transform: translateX(-6px);
}
.label-fade-leave-to {
  opacity: 0;
}
</style>
