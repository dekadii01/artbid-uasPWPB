<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import TheNavbar from './components/TheNavbar.vue'
import TheFooter from './components/TheFooter.vue'

const route = useRoute()
const showDefaultLayout = computed(() => {
  return route.meta.layout !== 'auth'
})
</script>

<template>
  <div class="min-h-screen bg-white text-black antialiased flex flex-col justify-between">
    <div>
      <TheNavbar v-if="showDefaultLayout" />
      <main>
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>
    <TheFooter v-if="showDefaultLayout" />
  </div>
</template>

<style>
/* Smooth route transition animations */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
