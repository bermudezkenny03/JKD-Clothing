<template>
  <Transition name="fade">
    <div v-if="isRouteLoading">
      <div class="loader-overlay">
        <DotLottieVue src="/images/gif/loader2.lottie" autoplay loop class="loader-lottie" />
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { DotLottieVue } from '@lottiefiles/dotlottie-vue'

defineProps<{ isRouteLoading: boolean }>()
</script>

<style scoped>
.loader-overlay {
  position: fixed;
  inset: 0;
  z-index: 999999;
  background: rgba(255, 255, 255, 0.45);
  backdrop-filter: blur(2px);
  display: flex;
  align-items: center;
  justify-content: center;
}

.loader-lottie {
  width: min(380px, 60vw);
  height: min(380px, 60vw);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
