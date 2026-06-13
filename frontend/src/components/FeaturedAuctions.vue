<script setup>
const auctions = [
  {
    image: '/img/art1.png',
    badgeType: 'live',
    badgeText: 'Live',
    timer: '02:00:00',
    category: 'Patung Ukir Tradisional · Kayu',
    title: 'Penjaga Bali',
    artist: 'I Made Wijaya',
    priceLabel: 'Tawaran saat ini',
    price: 'Rp 12.500.000',
    countLabel: 'Penawar',
    count: '18 orang',
    buttonText: 'Ikuti Lelang',
    buttonStyle: 'primary',
  },
  {
    image: '/img/art2.png',
    badgeType: 'live',
    badgeText: 'Live',
    timer: '05:22:14',
    category: 'Patung · Kayu Eboni',
    title: 'Dewi Kesuburan',
    artist: 'Ni Luh Eka Sari',
    priceLabel: 'Tawaran saat ini',
    price: 'Rp 28.000.000',
    countLabel: 'Penawar',
    count: '31 orang',
    buttonText: 'Ikuti Lelang',
    buttonStyle: 'primary',
  },
  {
    image: '/img/art3.png',
    badgeType: 'upcoming',
    badgeText: 'Segera',
    timer: 'Mulai 18:00',
    category: 'Fotografi Seni · Cetak Terbatas',
    title: 'Sunrise Penida',
    artist: 'Agung Rai Photography',
    priceLabel: 'Harga pembuka',
    price: 'Rp 5.000.000',
    countLabel: 'Diminati',
    count: '47 orang',
    buttonText: 'Ingatkan Saya',
    buttonStyle: 'outline',
  },
  {
    image: '/img/art4.png',
    badgeType: 'live',
    badgeText: 'Live',
    timer: '00:45:08',
    category: 'Keramik Tradisional · Kendi',
    title: 'Tanah & Api',
    artist: 'Ketut Suardana',
    priceLabel: 'Tawaran saat ini',
    price: 'Rp 8.750.000',
    countLabel: 'Penawar',
    count: '9 orang',
    buttonText: 'Ikuti Lelang',
    buttonStyle: 'primary',
  },
  {
    image: '/img/art5.png',
    badgeType: 'live',
    badgeText: 'Live',
    timer: '01:12:55',
    category: 'Lukisan Batik · Sutra',
    title: 'Alam Tak Terbatas',
    artist: 'Sang Ayu Putu Riani',
    priceLabel: 'Tawaran saat ini',
    price: 'Rp 19.200.000',
    countLabel: 'Penawar',
    count: '22 orang',
    buttonText: 'Ikuti Lelang',
    buttonStyle: 'primary',
  },
  {
    image: '/img/art6.png',
    badgeType: 'upcoming',
    badgeText: 'Segera',
    timer: 'Mulai Besok',
    category: 'Instalasi · Mixed Media',
    title: 'Jejak Digital',
    artist: 'Dewa Gede Artana',
    priceLabel: 'Harga pembuka',
    price: 'Rp 35.000.000',
    countLabel: 'Diminati',
    count: '61 orang',
    buttonText: 'Ingatkan Saya',
    buttonStyle: 'outline',
  },
]
</script>

<template>
  <section id="auctions" class="px-10 py-20">
    <div class="flex items-end justify-between mb-12">
      <div>
        <span class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400">Sedang Berlangsung</span>
        <h2 class="text-4xl font-bold mt-3">Lelang Unggulan</h2>
      </div>
      <a href="#" class="text-sm font-medium text-gray-600 hover:text-black flex items-center gap-1 transition-colors">
        Lihat Semua
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
        </svg>
      </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        v-for="auction in auctions"
        :key="auction.title"
        class="rounded-2xl overflow-hidden bg-white shadow-sm card-lift border border-gray-100"
      >
        <div class="relative aspect-4/3">
          <div class="h-full w-full bg-cover bg-center" :style="{ backgroundImage: `url('${auction.image}')` }"></div>
          <span
            v-if="auction.badgeType === 'live'"
            class="absolute top-4 left-4 bg-black text-white text-xs px-3 py-1 rounded-full flex items-center gap-1.5"
          >
            <span class="live-dot w-1.5 h-1.5 rounded-full bg-white inline-block"></span>{{ auction.badgeText }}
          </span>
          <span
            v-else
            class="absolute top-4 left-4 bg-gray-100 text-black text-xs px-3 py-1 rounded-full"
          >
            {{ auction.badgeText }}
          </span>
          <div class="absolute bottom-4 right-4 bg-black/70 backdrop-blur-sm text-white text-xs px-3 py-1.5 rounded-lg font-medium">
            {{ auction.timer }}
          </div>
        </div>
        <div class="p-5">
          <p class="text-xs text-gray-400 mb-1">{{ auction.category }}</p>
          <h3 class="font-semibold text-base">{{ auction.title }}</h3>
          <p class="text-gray-500 text-sm mt-0.5">{{ auction.artist }}</p>
          <div class="flex items-end justify-between mt-4 pt-4 border-t border-gray-100">
            <div>
              <p class="text-xs text-gray-400 mb-0.5">{{ auction.priceLabel }}</p>
              <p class="font-bold text-lg">{{ auction.price }}</p>
            </div>
            <div class="text-right">
              <p class="text-xs text-gray-400 mb-0.5">{{ auction.countLabel }}</p>
              <p class="font-semibold text-sm">{{ auction.count }}</p>
            </div>
          </div>
          <button
            :class="[
              'mt-4 w-full py-2.5 rounded-lg text-sm font-medium transition-colors',
              auction.buttonStyle === 'primary'
                ? 'bg-black text-white hover:bg-gray-800'
                : 'border border-black text-black hover:bg-black hover:text-white'
            ]"
          >
            {{ auction.buttonText }}
          </button>
        </div>
      </div>
    </div>
  </section>
</template>
