<script setup>
import { ref, computed, onMounted, onUnmounted, h, watch } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import { Icon } from "@iconify/vue";
import { getNotifications, markAsRead, markAllAsRead } from "../api/notifications";
import { getEcho } from "../api/echo";

const router = useRouter();
const auth = useAuthStore();

// ── State ──────────────────────────────────────────────────────────
const profileOpen = ref(false);
const mobileOpen = ref(false);
const profileRef = ref(null);

const notifOpen = ref(false);
const notifRef = ref(null);
const notifications = ref([]);

// ── User data — diambil langsung dari auth store ───────────────────
// auth.user di-set otomatis saat login/register/fetchUser berhasil.
const userName = computed(() => auth.user?.full_name ?? "");
const userEmail = computed(() => auth.user?.email ?? "");

const userInitials = computed(() =>
  userName.value
    .split(" ")
    .filter(Boolean)
    .map((w) => w[0])
    .slice(0, 2)
    .join("")
    .toUpperCase(),
);

// ── Nav links ─────────────────────────────────────────────────────
const navLinks = [
  { label: "Daftar Lelang", to: "/auctions" },
  { label: "Tawaran Saya", to: "/my-bids" },
  { label: "Watchlist", to: "/watchlist" },
  { label: "Karya Saya", to: "/my-auctions" },
];

// ── Profile dropdown menu ──────────────────────────────────────────
const profileMenuItems = [
  {
    label: "Profil Saya",
    to: "/profile",
    icon: {
      render: () =>
        iconPath(
          "M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z",
        ),
    },
  },
  {
    label: "Pengaturan",
    to: "/settings",
    icon: {
      render: () =>
        iconPath(
          "M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z",
        ),
    },
  },
];

// SVG icon helper (renders inline SVG via Vue render fn)
function iconPath(d) {
  return h(
    "svg",
    {
      class: "w-4 h-4",
      fill: "none",
      stroke: "currentColor",
      viewBox: "0 0 24 24",
    },
    [
      h("path", {
        "stroke-linecap": "round",
        "stroke-linejoin": "round",
        "stroke-width": "2",
        d,
      }),
    ],
  );
}

// ── Notifications ──────────────────────────────────────────────────
const unreadCount = computed(() => notifications.value.filter((n) => !n.read_at).length);

async function fetchNotifications() {
  if (!auth.isLoggedIn) return;
  try {
    const { data } = await getNotifications();
    notifications.value = data.data ?? [];
  } catch (err) {
    console.error("Gagal fetch notifikasi:", err);
  }
}

async function handleMarkAsRead(notif) {
  if (notif.read_at) return;
  try {
    await markAsRead(notif.id);
    notif.read_at = new Date().toISOString();
  } catch (err) {
    console.error("Gagal tandai dibaca:", err);
  }
}

async function handleMarkAllAsRead() {
  try {
    await markAllAsRead();
    notifications.value.forEach((n) => {
      n.read_at = new Date().toISOString();
    });
  } catch (err) {
    console.error("Gagal tandai semua dibaca:", err);
  }
}

let notifInterval;

// ── Logout ────────────────────────────────────────────────────────
async function handleLogout() {
  profileOpen.value = false;
  mobileOpen.value = false;

  // Cabut token di server + bersihkan state lokal (lihat stores/auth.js)
  await auth.logout();

  router.push("/login");
}

// ── Click-outside to close dropdown ───────────────────────────────
function handleClickOutside(e) {
  if (profileRef.value && !profileRef.value.contains(e.target)) {
    profileOpen.value = false;
  }
  if (notifRef.value && !notifRef.value.contains(e.target)) {
    notifOpen.value = false;
  }
}

let echoUserChannel = null;

function subscribeNotifications(userId) {
  if (echoUserChannel) return;
  try {
    const echo = getEcho();
    echoUserChannel = echo.private(`App.Models.User.${userId}`)
      .listen("NotificationSent", (e) => {
        console.log("Realtime notification received:", e);
        
        // Prepend the new notification
        notifications.value.unshift({
          id: e.id,
          user_id: e.user_id,
          type: e.type,
          channel: e.channel,
          title: e.title,
          body: e.body,
          data: e.data,
          read_at: e.read_at,
          created_at: e.created_at,
        });
      });
  } catch (err) {
    console.error("Gagal subscribe notifications:", err);
  }
}

function unsubscribeNotifications(userId) {
  if (echoUserChannel) {
    try {
      const echo = getEcho();
      echo.leave(`App.Models.User.${userId}`);
      echoUserChannel = null;
    } catch (err) {
      console.error("Gagal unsubscribe notifications:", err);
    }
  }
}

// Watch auth user state to subscribe/unsubscribe dynamically
watch(
  () => auth.user,
  (newUser, oldUser) => {
    if (newUser && newUser.id) {
      subscribeNotifications(newUser.id);
    } else if (oldUser && oldUser.id) {
      unsubscribeNotifications(oldUser.id);
    }
  },
  { immediate: true }
);

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
  fetchNotifications();
  // Poll setiap 30 detik (as a fallback)
  notifInterval = setInterval(fetchNotifications, 30000);
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
  clearInterval(notifInterval);
  if (auth.user && auth.user.id) {
    unsubscribeNotifications(auth.user.id);
  }
});</script>

<template>
  <nav
    class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-100"
  >
    <div class="hidden md:flex items-center justify-between px-10 py-4">
      <!-- Logo -->
      <router-link to="/" class="text-xl font-bold tracking-tight text-black">
        ArtBid<span class="font-light">Bali</span>
      </router-link>

      <!-- Nav links -->
      <ul class="flex items-center gap-1">
        <li v-for="link in navLinks" :key="link.to">
          <router-link
            :to="link.to"
            class="px-4 py-2 rounded-lg text-sm text-gray-500 hover:text-black hover:bg-gray-50 transition-all duration-200"
            active-class="text-black bg-gray-100 font-medium"
          >
            {{ link.label }}
          </router-link>
        </li>
      </ul>

      <!-- Right actions -->
      <div class="flex items-center gap-3">
        <!-- Notifications -->
        <div class="relative" ref="notifRef">
          <button
            @click="notifOpen = !notifOpen"
            class="relative w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:border-black hover:text-black transition-colors"
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
            <!-- Unread dot -->
            <span
              v-if="unreadCount > 0"
              class="absolute top-1.5 right-1.5 w-2 h-2 bg-black rounded-full border-2 border-white animate-pulse"
            ></span>
          </button>

          <!-- Dropdown Notifikasi -->
          <transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
          >
            <div
              v-if="notifOpen"
              class="absolute right-0 top-full mt-2 w-80 bg-white rounded-xl border border-gray-100 shadow-lg overflow-hidden py-1.5 z-50 notif-dropdown"
            >
              <div class="px-4 py-2.5 border-b border-gray-100 flex items-center justify-between">
                <p class="text-sm font-semibold text-gray-900">Notifikasi</p>
                <button
                  v-if="unreadCount > 0"
                  @click="handleMarkAllAsRead"
                  class="text-[10px] text-gray-400 hover:text-black transition-colors"
                >
                  Tandai semua dibaca
                </button>
              </div>

              <!-- List notifikasi -->
              <div class="max-h-64 overflow-y-auto custom-scroll">
                <div v-if="notifications.length === 0" class="px-4 py-8 text-center text-xs text-gray-400">
                  Tidak ada notifikasi.
                </div>
                <div
                  v-else
                  v-for="notif in notifications"
                  :key="notif.id"
                  @click="handleMarkAsRead(notif); notifOpen = false; $router.push(notif.data?.auction_id ? `/auction/${notif.data.auction_id}` : '/')"
                  class="px-4 py-3 border-b border-gray-50 last:border-b-0 hover:bg-gray-50 cursor-pointer transition-colors flex items-start gap-2.5 text-left"
                  :class="{ 'bg-gray-50/50 font-medium': !notif.read_at }"
                >
                  <!-- Icon -->
                  <div
                    class="w-7 h-7 rounded-lg shrink-0 flex items-center justify-center mt-0.5"
                    :class="[
                      notif.type === 'outbid' ? 'bg-red-50 text-red-600' :
                      notif.type === 'auction_won' ? 'bg-amber-50 text-amber-600' :
                      'bg-black text-white'
                    ]"
                  >
                    <Icon
                      :icon="
                        notif.type === 'outbid' ? 'mdi:alert-circle' :
                        notif.type === 'auction_won' ? 'mdi:trophy' :
                        'mdi:bell'
                      "
                      class="w-4 h-4"
                    />
                  </div>
                  <!-- Content -->
                  <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-900 leading-snug">{{ notif.title }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5 line-clamp-2">{{ notif.body }}</p>
                  </div>
                  <!-- Unread indicator -->
                  <span v-if="!notif.read_at" class="w-1.5 h-1.5 bg-black rounded-full shrink-0 mt-2"></span>
                </div>
              </div>
            </div>
          </transition>
        </div>

        <!-- Divider -->
        <div class="w-px h-5 bg-gray-200"></div>

        <!-- User avatar / profile dropdown -->
        <div class="relative" ref="profileRef">
          <button
            @click="profileOpen = !profileOpen"
            class="flex items-center gap-2.5 pl-1 pr-3 py-1 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <div
              class="w-7 h-7 rounded-lg bg-gray-900 flex items-center justify-center text-white text-xs font-bold shrink-0"
            >
              {{ userInitials }}
            </div>
            <span class="text-sm font-medium text-gray-800">{{
              userName
            }}</span>
            <svg
              class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200"
              :class="{ 'rotate-180': profileOpen }"
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

          <!-- Dropdown -->
          <transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
          >
            <div
              v-if="profileOpen"
              class="absolute right-0 top-full mt-2 w-52 bg-white rounded-xl border border-gray-100 shadow-lg overflow-hidden py-1.5"
            >
              <div class="px-4 py-2.5 border-b border-gray-100">
                <p class="text-sm font-semibold text-gray-900">
                  {{ userName }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">{{ userEmail }}</p>
              </div>
              <div class="py-1">
                <router-link
                  v-for="item in profileMenuItems"
                  :key="item.label"
                  :to="item.to"
                  @click="profileOpen = false"
                  class="flex items-center gap-3 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-black transition-colors"
                >
                  <component :is="item.icon" class="w-4 h-4 shrink-0" />
                  {{ item.label }}
                </router-link>
              </div>
              <div class="border-t border-gray-100 py-1">
                <button
                  @click="handleLogout"
                  class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors"
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
                  Keluar
                </button>
              </div>
            </div>
          </transition>
        </div>

        <!-- Buat Lelang CTA -->
        <!-- <button
          @click="router.push('/auction/create')"
          class="cursor-pointer px-5 py-2 bg-black text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors"
        >
          + Buat Lelang
        </button> -->
      </div>
    </div>

    <!-- ── MOBILE ──────────────────────────────────────────────── -->
    <div class="md:hidden px-5 py-3.5 flex items-center justify-between">
      <router-link to="/" class="text-lg font-bold tracking-tight">
        ArtBid<span class="font-light">Bali</span>
      </router-link>

      <div class="flex items-center gap-2">
        <!-- Notifications mobile -->
        <div class="relative">
          <button
            @click="notifOpen = !notifOpen"
            class="relative w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500"
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
            <!-- Unread dot -->
            <span
              v-if="unreadCount > 0"
              class="absolute top-1.5 right-1.5 w-2 h-2 bg-black rounded-full border-2 border-white animate-pulse"
            ></span>
          </button>

          <!-- Dropdown Notifikasi Mobile -->
          <transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
          >
            <div
              v-if="notifOpen"
              class="absolute right-0 top-full mt-2 w-72 bg-white rounded-xl border border-gray-100 shadow-lg overflow-hidden py-1.5 z-50 notif-dropdown"
            >
              <div class="px-4 py-2 flex items-center justify-between border-b border-gray-100">
                <p class="text-xs font-semibold text-gray-950">Notifikasi</p>
                <button
                  v-if="unreadCount > 0"
                  @click="handleMarkAllAsRead"
                  class="text-[9px] text-gray-400 hover:text-black transition-colors"
                >
                  Semua dibaca
                </button>
              </div>

              <!-- List notifikasi -->
              <div class="max-h-60 overflow-y-auto custom-scroll">
                <div v-if="notifications.length === 0" class="px-4 py-6 text-center text-xs text-gray-400">
                  Tidak ada notifikasi.
                </div>
                <div
                  v-else
                  v-for="notif in notifications"
                  :key="notif.id"
                  @click="handleMarkAsRead(notif); notifOpen = false; mobileOpen = false; $router.push(notif.data?.auction_id ? `/auction/${notif.data.auction_id}` : '/')"
                  class="px-4 py-2.5 border-b border-gray-50 last:border-b-0 hover:bg-gray-50 cursor-pointer transition-colors flex items-start gap-2 text-left"
                  :class="{ 'bg-gray-50/50 font-medium': !notif.read_at }"
                >
                  <div
                    class="w-6 h-6 rounded-lg shrink-0 flex items-center justify-center mt-0.5"
                    :class="[
                      notif.type === 'outbid' ? 'bg-red-50 text-red-600' :
                      notif.type === 'auction_won' ? 'bg-amber-50 text-amber-600' :
                      'bg-black text-white'
                    ]"
                  >
                    <Icon
                      :icon="
                        notif.type === 'outbid' ? 'mdi:alert-circle' :
                        notif.type === 'auction_won' ? 'mdi:trophy' :
                        'mdi:bell'
                      "
                      class="w-3 h-3"
                    />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-[11px] text-gray-900 leading-snug">{{ notif.title }}</p>
                    <p class="text-[9px] text-gray-400 mt-0.5 line-clamp-2">{{ notif.body }}</p>
                  </div>
                </div>
              </div>
            </div>
          </transition>
        </div>

        <!-- Hamburger -->
        <button
          @click="mobileOpen = !mobileOpen"
          class="w-9 h-9 rounded-lg border border-gray-200 flex flex-col items-center justify-center gap-1.5"
        >
          <span
            class="w-4.5 h-0.5 bg-gray-800 block transition-all duration-300 origin-center"
            :class="mobileOpen ? 'rotate-45 translate-y-2' : ''"
          ></span>
          <span
            class="w-4.5 h-0.5 bg-gray-800 block transition-all duration-300"
            :class="mobileOpen ? 'opacity-0 scale-x-0' : ''"
          ></span>
          <span
            class="w-4.5 h-0.5 bg-gray-800 block transition-all duration-300 origin-center"
            :class="mobileOpen ? '-rotate-45 -translate-y-2' : ''"
          ></span>
        </button>
      </div>
    </div>

    <!-- Mobile menu panel -->
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div
        v-if="mobileOpen"
        class="md:hidden border-t border-gray-100 bg-white px-5 pb-5 pt-3"
      >
        <!-- User info -->
        <div class="flex items-center gap-3 pb-4 border-b border-gray-100 mb-3">
          <div
            class="w-9 h-9 rounded-xl bg-gray-900 flex items-center justify-center text-white text-sm font-bold"
          >
            {{ userInitials }}
          </div>
          <div>
            <p class="text-sm font-semibold">{{ userName }}</p>
            <p class="text-xs text-gray-400">{{ userEmail }}</p>
          </div>
        </div>

        <!-- Nav links mobile -->
        <ul class="space-y-1 mb-4">
          <li v-for="link in navLinks" :key="link.to">
            <router-link
              :to="link.to"
              @click="mobileOpen = false"
              class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-50 hover:text-black transition-colors"
              active-class="bg-gray-100 text-black font-medium"
            >
              {{ link.label }}
            </router-link>
          </li>
        </ul>

        <!-- Profile links mobile -->
        <ul class="space-y-1 border-t border-gray-100 pt-3 mb-4">
          <li v-for="item in profileMenuItems" :key="item.label">
            <router-link
              :to="item.to"
              @click="mobileOpen = false"
              class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-600 hover:bg-gray-50 hover:text-black transition-colors"
            >
              {{ item.label }}
            </router-link>
          </li>
        </ul>

        <!-- CTA + Logout -->
        <div class="flex gap-2 pt-2 border-t border-gray-100">
          <!-- <button
            @click="router.push('/auction/create')"
            class="cursor-pointer flex-1 py-2.5 bg-black text-white rounded-lg text-sm font-medium"
          >
            + Buat Lelang
          </button> -->
          <button
            @click="handleLogout"
            class="px-4 py-2.5 border border-red-100 text-red-500 rounded-lg text-sm font-medium hover:bg-red-50 transition-colors"
          >
            Keluar
          </button>
        </div>
      </div>
    </transition>
  </nav>
</template>

<style scoped>
/* Pastikan DM Sans sudah di-import global di project kamu */
nav {
  font-family: "DM Sans", sans-serif;
}
</style>
