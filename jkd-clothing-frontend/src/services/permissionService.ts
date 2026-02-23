import apiFetch from './apiService'

const API_URL = import.meta.env.VITE_API_URL

export const permissionService = {
    async getGeneralData() {
        try {
            const response = await apiFetch(`${API_URL}/permissions/general-data`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
            })

            if (!response.ok) {
                throw new Error('Error in getGeneralData')
            }

            const data = await response.json()
            return data
        } catch (error) {
            console.error('Error in getGeneralData:', error)
            return []
        }
    },

    async getRolePermissions(roleId: string) {
        try {
            const response = await apiFetch(`${API_URL}/permissions/roles/${roleId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            })

            if (!response.ok) {
                throw new Error('Error in getRolePermissions')
            }

            const data = await response.json()
            return data
        } catch (error) {
            console.error('Error in getRolePermissions:', error)
            throw error
        }
    },

    async assignPermissions(roleId: string, payload: object) {
        try {
            const response = await apiFetch(`${API_URL}/permissions/roles/${roleId}/assign`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            })

            if (!response.ok) {
                throw new Error('Error in assignPermissions')
            }

            const data = await response.json()
            return data
        } catch (error) {
            console.error('Error in assignPermissions:', error)
            throw error
        }
    },
}
