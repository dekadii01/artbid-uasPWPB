<template>
  <div class="min-h-screen bg-gray-50 font-sans flex">
    <AdminSidebar />
    <div
      class="flex-1 flex flex-col min-w-0 transition-all duration-300"
      :style="{ marginLeft: sidebarWidth }"
    >
      <router-view />
      <!-- ← BUKAN <slot />, karena ini layout via router children -->
    </div>
  </div>
</template>

<script setup>
import { ref, computed, provide } from "vue";
import AdminSidebar from "../components/admin/AdminSidebar.vue";

const collapsed = ref(false);
provide("sidebarCollapsed", collapsed);

const sidebarWidth = computed(() => (collapsed.value ? "" : ""));
</script>
