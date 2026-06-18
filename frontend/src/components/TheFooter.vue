<script setup>
const currentYear = new Date().getFullYear();

const footerLinks = [
  { label: "Beranda", href: "#home" },
  { label: "Lelang Aktif", href: "#auctions" },
  { label: "Cara Kerja", href: "#how-it-works" },
  { label: "Jual Karya", href: "#sellers" },
  { label: "FAQ", href: "#faq" },
];

const legalLinks = [
  { label: "Syarat & Ketentuan", href: "#" },
  { label: "Kebijakan Privasi", href: "#" },
  { label: "Kebijakan Escrow", href: "#" },
];
</script>

<template>
  <footer
    class="bg-black text-white/60 pt-16 pb-10 px-10 border-t border-white/10"
  >
    <div class="mx-auto grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
      <!-- Brand column -->
      <div class="space-y-5">
        <div class="text-2xl font-bold text-white tracking-tight">
          ArtBid<span class="font-light">Bali</span>
        </div>
        <p class="text-sm leading-relaxed">
          Platform lelang online eksklusif untuk kolektor dan seniman Bali.
          Ajukan penawaran secara realtime, aman, dan transparan dari mana saja.
        </p>
      </div>

      <!-- Links column -->
      <div>
        <h4
          class="text-white font-semibold text-sm mb-5 uppercase tracking-wider"
        >
          Navigasi
        </h4>
        <ul class="space-y-3 text-sm">
          <li v-for="link in footerLinks" :key="link.label">
            <a
              :href="link.href"
              class="hover:text-white transition-colors duration-200"
            >
              {{ link.label }}
            </a>
          </li>
        </ul>
      </div>

      <!-- Contact column -->
      <div>
        <h4
          class="text-white font-semibold text-sm mb-5 uppercase tracking-wider"
        >
          Hubungi Kami
        </h4>
        <ul class="space-y-3 text-sm">
          <li>
            Email:
            <a
              href="mailto:info@artbidbali.com"
              class="hover:text-white transition-colors"
              >info@artbidbali.com</a
            >
          </li>
          <li>
            Telp:
            <a
              href="tel:+62361123456"
              class="hover:text-white transition-colors"
              >+62 361 123 456</a
            >
          </li>
          <li class="leading-relaxed">
            Alamat: Jl. Raya Ubud No. 88, Ubud, Gianyar, Bali
          </li>
        </ul>
      </div>

      <!-- Social column -->
      <div>
        <h4
          class="text-white font-semibold text-sm mb-5 uppercase tracking-wider"
        >
          Ikuti Kami
        </h4>
        <div class="flex gap-4">
          <a
            href="#"
            class="w-10 h-10 bg-white/10 hover:bg-white/20 text-white rounded-xl flex items-center justify-center transition-colors"
          >
            <!-- Instagram icon -->
            <svg
              class="w-5 h-5"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <rect width="20" height="20" x="2" y="2" rx="5" ry="5" />
              <path
                d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zM17.5 6.5h.01"
              />
            </svg>
          </a>
          <a
            href="#"
            class="w-10 h-10 bg-white/10 hover:bg-white/20 text-white rounded-xl flex items-center justify-center transition-colors"
          >
            <!-- Facebook icon -->
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path
                d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"
              />
            </svg>
          </a>
          <a
            href="#"
            class="w-10 h-10 bg-white/10 hover:bg-white/20 text-white rounded-xl flex items-center justify-center transition-colors"
          >
            <!-- X/Twitter icon -->
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path
                d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"
              />
            </svg>
          </a>
        </div>
      </div>
    </div>

    <!-- Bottom footer -->
    <div
      class="max-w-7xl mx-auto pt-8 border-t border-white/10 flex flex-col md:flex-row items-center justify-between gap-4 text-xs"
    >
      <p>&copy; {{ currentYear }} ArtBid Bali. Hak Cipta Dilindungi.</p>
      <div class="flex gap-6">
        <a
          v-for="link in legalLinks"
          :key="link.label"
          :href="link.href"
          class="hover:text-white transition-colors"
        >
          {{ link.label }}
        </a>
      </div>
    </div>
  </footer>
</template>
