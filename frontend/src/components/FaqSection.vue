<script setup>
import { ref } from 'vue'

const faqItems = [
  {
    question: 'Apakah mendaftar ke ArtBid Bali gratis?',
    answer: 'Ya, pendaftaran di ArtBid Bali 100% gratis baik untuk pembeli maupun penjual. Anda hanya perlu membuat akun dan memverifikasi identitas Anda untuk mulai menawar atau menjual karya.'
  },
  {
    question: 'Bagaimana cara kerja sistem penawaran (bidding)?',
    answer: 'Setiap lelang memiliki waktu hitung mundur yang berjalan secara realtime. Anda dapat memasukkan tawaran Anda secara instan. Jika ada tawaran baru masuk dalam 2 menit terakhir sebelum lelang selesai, durasi lelang akan otomatis diperpanjang selama 2 menit tambahan untuk menghindari sniper bidding.'
  },
  {
    question: 'Apakah karya seni yang dijual dijamin keasliannya?',
    answer: 'Tentu saja. Semua karya seni yang terdaftar di ArtBid Bali melalui proses kurasi ketat dan diverifikasi langsung dari senimannya. Setiap karya yang terjual dilengkapi dengan Sertifikat Keaslian (Certificate of Authenticity) resmi.'
  },
  {
    question: 'Bagaimana proses pengiriman karya seni setelah menang lelang?',
    answer: 'Pengiriman akan diatur oleh tim logistik kami yang berpengalaman dalam menangani karya seni. Karya akan dikemas secara profesional menggunakan kayu/palet pelindung dan dikirimkan dengan asuransi penuh untuk menjamin keamanannya hingga tiba di lokasi Anda.'
  },
  {
    question: 'Bagaimana sistem pembayaran di ArtBid Bali?',
    answer: 'Kami menyediakan sistem pembayaran escrow (rekening bersama) yang aman. Setelah Anda menang lelang, dana pembayaran Anda akan ditahan oleh sistem kami dan baru akan diteruskan ke penjual setelah Anda mengonfirmasi penerimaan karya seni dalam kondisi baik.'
  }
]

const activeIndex = ref(null)

function toggleFaq(index) {
  if (activeIndex.value === index) {
    activeIndex.value = null
  } else {
    activeIndex.value = index
  }
}
</script>

<template>
  <section id="faq" class="px-10 py-20">
    <div class="max-w-2xl mx-auto">
      <div class="text-center mb-12">
        <span class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400">Pertanyaan Umum</span>
        <h2 class="text-4xl font-bold mt-3">FAQ</h2>
      </div>

      <div class="space-y-3">
        <div
          v-for="(item, index) in faqItems"
          :key="index"
          class="border border-gray-200 rounded-xl overflow-hidden"
        >
          <button
            @click="toggleFaq(index)"
            class="w-full flex items-center justify-between px-6 py-5 text-left hover:bg-gray-50 transition-colors"
          >
            <span class="font-medium text-sm pr-4">{{ item.question }}</span>
            <svg
              :class="['faq-icon w-4 h-4 text-gray-400 shrink-0 transition-transform duration-300', { 'rotated': activeIndex === index }]"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div
            :class="['accordion-content transition-all duration-300 ease-in-out', { 'open': activeIndex === index }]"
            :style="{ maxHeight: activeIndex === index ? '300px' : '0px' }"
          >
            <div class="px-6 pb-5">
              <p class="text-gray-500 text-sm leading-relaxed">
                {{ item.answer }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
