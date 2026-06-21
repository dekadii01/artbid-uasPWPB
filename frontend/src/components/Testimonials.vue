<script setup>
const testimonials = [
  {
    rating: 5,
    text: 'Platform ini benar-benar mengubah cara saya mengoleksi karya seni. Prosesnya transparan dan saya bisa melihat semua tawaran secara langsung. Sudah 8 karya saya dapatkan dari sini.',
    avatar: 'https://i.pravatar.cc/40?img=25',
    name: 'Budi Santoso',
    role: 'Kolektor Senior, Denpasar',
    bg: 'bg-gray-50 text-black border border-gray-100',
    textColor: 'text-gray-600',
    roleColor: 'text-gray-400',
    starColor: 'text-gray-900',
    avatarBorder: 'border-2 border-gray-200'
  },
  {
    rating: 5,
    text: 'Sebagai seniman, saya ragu di awal. Tapi setelah mencoba, lukisan pertama saya terjual di harga 3× lipat perkiraan saya. Komunitas di sini serius dan menghargai karya dengan harga yang layak.',
    avatar: 'https://i.pravatar.cc/40?img=49',
    name: 'Ni Wayan Artini',
    role: 'Seniman & Penjual, Ubud',
    bg: 'bg-black text-white',
    textColor: 'text-white/80',
    roleColor: 'text-white/40',
    starColor: 'text-white',
    avatarBorder: 'border-2 border-white/20'
  },
  {
    rating: 5,
    text: 'Notifikasi realtime-nya luar biasa. Saya hampir melewatkan momen terakhir, tapi sistem otomatis mengirim peringatan dan saya berhasil memenangkan karya itu di detik-detik akhir!',
    avatar: 'https://i.pravatar.cc/40?img=36',
    name: 'Gede Mahendra',
    role: 'Kolektor Muda, Seminyak',
    bg: 'bg-gray-50 text-black border border-gray-100',
    textColor: 'text-gray-600',
    roleColor: 'text-gray-400',
    starColor: 'text-gray-900',
    avatarBorder: 'border-2 border-gray-200'
  }
]
</script>

<template>
  <section class="px-4 md:px-10 py-20">
    <div class="text-center mb-14">
      <span class="text-xs font-semibold tracking-[0.2em] uppercase text-gray-400">Kata Mereka</span>
      <h2 class="text-4xl font-bold mt-3">Dipercaya Kolektor & Seniman</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        v-for="testimonial in testimonials"
        :key="testimonial.name"
        :class="[testimonial.bg, 'rounded-2xl p-7 card-lift']"
      >
        <div class="flex gap-1 mb-5">
          <svg
            v-for="i in testimonial.rating"
            :key="i"
            :class="[testimonial.starColor, 'w-4 h-4']"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
            />
          </svg>
        </div>
        <p :class="[testimonial.textColor, 'text-sm leading-relaxed']">
          {{ testimonial.text }}
        </p>
        <div class="flex items-center gap-3 mt-6">
          <div
            :class="[testimonial.avatarBorder, 'w-10 h-10 rounded-full bg-gray-500 bg-cover bg-center']"
            :style="{ backgroundImage: `url('${testimonial.avatar}')` }"
          ></div>
          <div>
            <p class="font-medium text-sm">{{ testimonial.name }}</p>
            <p :class="[testimonial.roleColor, 'text-xs']">{{ testimonial.role }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
