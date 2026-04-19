import FourZeroFour from '@/views/Errors/FourZeroFour.vue'
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

const baseUrl = '/'

const router = createRouter({
  history: createWebHistory(baseUrl),
  scrollBehavior(to, from, savedPosition) {
    return savedPosition || { left: 0, top: 0 }
  },
  routes: [
    {
      path: '/',
      name: 'Home',
      component: () => import('../views/Users/Users.vue'),
      meta: {
        title: 'Home',
        requiresAuth: true,
        module: 'users',
      },
    },

    {
      path: '/signin',
      name: 'Signin',
      component: () => import('../views/Auth/Signin.vue'),
      meta: {
        title: 'Signin',
      },
    },

    {
      path: '/dashboard',
      name: 'Dashboard',
      component: () => import('../views/Ecommerce.vue'),
      meta: {
        title: 'Dashboard',
        requiresAuth: true,
        module: 'dashboard',
      },
    },

    {
      path: '/products',
      name: 'Products',
      component: () => import('../views/Users/Users.vue'),
      meta: {
        title: 'Products',
        requiresAuth: true,
        module: 'products',
      },
    },

    {
      path: '/users',
      name: 'Users',
      component: () => import('../views/Users/Users.vue'),
      meta: {
        title: 'Users',
        requiresAuth: true,
        module: 'users',
      },
    },

    {
      path: '/settings',
      name: 'Settings',
      component: () => import('../views/Settings/Settings.vue'),
      meta: {
        title: 'Settings',
        requiresAuth: true,
        module: 'settings',
      },
    },

    // ERROR ROUTES
    {
      path: '/error-404',
      name: '404 Error',
      component: FourZeroFour,
      meta: {
        title: '404 Error',
      },
    },

    {
      path: '/:pathMatch(.*)*',
      redirect: '/error-404',
    },
  ],
})

// ROUTER GUARDS CON VERIFICACIÓN DE PERMISOS
router.beforeEach((to, from, next) => {
  document.title = `${to.meta.title} | JKD Clothing`

  const authStore = useAuthStore()
  const requiresAuth = to.matched.some((record) => record.meta.requiresAuth)
  const isAuthenticated = !!authStore.token

  // si requiere auth y no está logueado
  if (requiresAuth && !isAuthenticated) {
    next({ name: 'Signin' })
    return
  }

  // Redirección desde home
  if (to.path === '/') {
    if (isAuthenticated) {
      const firstModule = authStore.modules[0]

      if (firstModule?.children?.length) {
        next(firstModule.children[0].route!)
      } else if (firstModule?.route) {
        next(firstModule.route)
      } else {
        next('/dashboard')
      }
    } else {
      next({ name: 'Signin' })
    }
    return
  }

  // Verificar permisos
  if (requiresAuth && to.meta.module) {
    const moduleSlug = to.meta.module as string
    const requiredPermission = to.meta.permission as string | undefined

    if (!authStore.hasModule(moduleSlug)) {
      next('/error-404')
      return
    }

    if (requiredPermission && !authStore.hasPermission(moduleSlug, requiredPermission)) {
      next('/error-404')
      return
    }
  }

  next()
})

export default router
