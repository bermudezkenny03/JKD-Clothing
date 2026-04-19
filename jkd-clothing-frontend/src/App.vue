<template>
  <ThemeProvider>
    <SidebarProvider>
      <RouteLoader :isRouteLoading="isRouteLoading" />
      <RouterView />
    </SidebarProvider>
  </ThemeProvider>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import ThemeProvider from './components/layout/ThemeProvider.vue'
import SidebarProvider from './components/layout/SidebarProvider.vue'
import RouteLoader from '@/components/common/RouteLoader.vue'

const router = useRouter()
const isRouteLoading = ref(false)

let timeout: ReturnType<typeof setTimeout> | null = null

router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth) {
    timeout = setTimeout(() => {
      isRouteLoading.value = true
    }, 200)
  }
  next()
})

router.afterEach(() => {
  if (timeout) clearTimeout(timeout)
  isRouteLoading.value = false
})

</script>
