import { defineStore } from 'pinia'
import { authService } from '@/services/authService'
import type { User, Module } from '@/utils/interfaces'

interface AuthState {
  token: string | null
  expiresAt: string | null // ← nuevo
  user: User | null
  modules: Module[]
}

export const useAuthStore = defineStore('authStore', {
  state: (): AuthState => ({
    token: null,
    expiresAt: null, // ← nuevo
    user: null,
    modules: [],
  }),

  getters: {
    isAuthenticated: (state): boolean => !!state.token && !!state.user,

    isTokenExpired: (state): boolean => {
      if (!state.expiresAt) return false
      return new Date(state.expiresAt) <= new Date()
    },

    hasModule: (state) => {
      return (moduleSlug: string): boolean => {
        return state.modules.some(
          (module) =>
            module.slug === moduleSlug ||
            module.children?.some((child) => child.slug === moduleSlug),
        )
      }
    },

    hasPermission: (state) => {
      return (moduleSlug: string, permissionSlug: string): boolean => {
        const module = state.modules.find((m) => m.slug === moduleSlug)
        if (module?.permissions.includes(permissionSlug)) return true

        for (const parentModule of state.modules) {
          const childModule = parentModule.children?.find((child) => child.slug === moduleSlug)
          if (childModule?.permissions.includes(permissionSlug)) return true
        }
        return false
      }
    },

    getModuleInfo: (state) => {
      return (moduleSlug: string): Module | undefined => {
        const module = state.modules.find((m) => m.slug === moduleSlug)
        if (module) return module

        for (const parentModule of state.modules) {
          const childModule = parentModule.children?.find((child) => child.slug === moduleSlug)
          if (childModule) return childModule
        }
        return undefined
      }
    },
  },

  actions: {
    async login(email: string, password: string): Promise<void> {
      const { access_token, expires_at, user, modules } = await authService.login(email, password)

      this.setAccessToken(access_token)
      this.setExpiresAt(expires_at)
      this.setUserData(user)
      this.setModules(modules)
    },

    async logout(): Promise<void> {
      try {
        await authService.logout()
      } finally {
        this.clearAuthData()
      }
    },

    setAccessToken(token: string): void {
      this.token = token
    },

    setExpiresAt(expiresAt: string): void {
      this.expiresAt = expiresAt
    },

    setUserData(user: User): void {
      this.user = user
    },

    setModules(modules: Module[]): void {
      this.modules = modules
    },

    clearAuthData(): void {
      this.token = null
      this.expiresAt = null
      this.user = null
      this.modules = []
    },
  },

  persist: true,
})
