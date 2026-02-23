import apiFetch from '@/services/apiService'

const API_URL = import.meta.env.VITE_API_URL

export const authService = {
    async login(email: string, password: string) {
        const response = await fetch(`${API_URL}/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password }),
        })

        if (!response.ok) {
            const errorData = await response.json()
            throw new Error(errorData.message || 'Login failed')
        }

        const data = await response.json()
        return data
    },

    async refreshToken() {
        const response = await apiFetch(`${API_URL}/refresh`, {
            method: 'POST',
        })

        if (!response.ok) {
            const errorData = await response.json()
            throw new Error(errorData.error || 'Refresh failed')
        }

        const data = await response.json()
        return {
            token: data.access_token,
            expiresIn: data.expires_in,
            tokenType: data.token_type,
        }
    },

    async logout(): Promise<void> {
        const response = await apiFetch(`${API_URL}/logout`, {
            method: 'POST',
        })

        if (!response.ok) {
            const errorData = await response.json()
            throw new Error(errorData.error || 'Logout failed')
        }
    },
}
