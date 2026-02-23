import { defineStore } from 'pinia'
import { authService } from '@/services/authService'
import type { User, Module } from '@/utils/interfaces'

interface AuthState {
    token: string | null
    user: User | null
    modules: Module[]
}

export const useAuthStore = defineStore('authStore', {
    state: (): AuthState => ({
        token: null,
        user: null,
        modules: [],
    }),


    getters: {
        isAuthenticated: (state): boolean => !!state.token && !!state.user,

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
                if (module?.permissions.includes(permissionSlug)) {
                    return true
                }

                for (const parentModule of state.modules) {
                    const childModule = parentModule.children?.find((child) => child.slug === moduleSlug)
                    if (childModule?.permissions.includes(permissionSlug)) {
                        return true
                    }
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
            try {
                const { token, user, modules } = await authService.login(email, password)

                this.setAccessToken(token)
                this.setUserData(user)
                this.setModules(modules)
            } catch (error) {
                throw error
            }
        },
        async logout(): Promise<void> {
            try {
                await authService.logout()
                this.clearAuthData()
            } catch (error) {
                throw error
            }
        },

        setAccessToken(token: string): void {
            this.token = token
        },

        setUserData(user: User): void {
            this.user = user
        },

        setModules(modules: Module[]): void {
            // Fix: Proper typing
            this.modules = modules
        },

        clearAuthData(): void {
            this.token = null
            this.user = null
            this.modules = []
        },
    },

    persist: true,
})
