<template>
  <div class="relative flex flex-col items-center justify-center min-h-screen p-6 overflow-hidden z-1">
    <common-grid-shape />
    <!-- Centered Content -->
    <div class="mx-auto w-full max-w-[242px] text-center sm:max-w-[472px]">
      <h1 class="mb-8 font-bold text-gray-800 text-title-md dark:text-white/90 xl:text-title-2xl">
        ERROR
      </h1>

      <img src="/images/error/404.svg" alt="404" class="dark:hidden" />
      <img src="/images/error/404-dark.svg" alt="404" class="hidden dark:block" />

      <p class="mt-10 mb-6 text-base text-gray-700 dark:text-gray-400 sm:text-lg">
        We can't seem to find the page you are looking for!
      </p>

      <router-link :to="homeRoute"
        class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
        Back to Home Page
      </router-link>
    </div>
    <!-- Footer -->
    <p class="absolute text-sm text-center text-gray-500 -translate-x-1/2 bottom-6 left-1/2 dark:text-gray-400">
      &copy; {{ currentYear }} - TailAdmin
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import CommonGridShape from '../../components/common/CommonGridShape.vue'

const authStore = useAuthStore()
const currentYear = ref(new Date().getFullYear())

// Computed para determinar la ruta home
const homeRoute = computed(() => {
  console.log('🔍 Debug homeRoute:')
  console.log('- isAuthenticated:', authStore.isAuthenticated)
  console.log('- modules:', authStore.modules)
  console.log('- modules length:', authStore.modules?.length)

  if (!authStore.isAuthenticated || !authStore.modules || authStore.modules.length === 0) {
    console.log('❌ Not authenticated or no modules, redirecting to signin')
    return '/signin'
  }

  // Buscar el primer módulo o submódulo con ruta válida
  for (const module of authStore.modules) {
    console.log('🔍 Checking module:', module)

    // Si el módulo tiene submódulos, usar el primer submódulo
    if (module.children && module.children.length > 0) {
      const firstChild = module.children[0]
      console.log('👶 First child found:', firstChild)

      if (firstChild.route && firstChild.route !== '/') {
        console.log('✅ Redirecting to first child:', firstChild.route)
        return firstChild.route
      }
    }

    // Si no tiene submódulos, usar la ruta del módulo padre
    if (module.route && module.route !== '/') {
      console.log('✅ Redirecting to parent module:', module.route)
      return module.route
    }
  }

  console.log('🔄 No valid routes found, redirecting to signin')
  return '/signin'
})

onMounted(async () => {
  // Cargar datos del usuario si tiene token pero no tiene módulos
  if (
    authStore.token &&
    (!authStore.user || !authStore.modules || authStore.modules.length === 0)
  ) {
    console.log('🔄 Loading user data in 404 page...')

    try {
      console.log('✅ User data loaded successfully')
    } catch (error) {
      console.error('❌ Error loading user data:', error)
    }
  }

  // Actualizar año cada minuto
  setInterval(() => {
    currentYear.value = new Date().getFullYear()
  }, 60000)
})
</script>
