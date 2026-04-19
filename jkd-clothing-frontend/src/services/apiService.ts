import router from '@/router'
import { useAuthStore } from '@/stores/authStore'

const API_URL = import.meta.env.VITE_API_URL

let isRefreshing = false

type FailedRequest = {
  resolve: (value: Response | PromiseLike<Response>) => void
  reject: (reason?: unknown) => void
  input: RequestInfo | URL
  init?: RequestInit
}

let failedQueue: FailedRequest[] = []

const buildHeaders = (
  headers?: HeadersInit,
  token?: string,
  extraHeaders?: Record<string, string>
): Headers => {
  const finalHeaders = new Headers(headers)

  if (token) {
    finalHeaders.set('Authorization', `Bearer ${token}`)
  }

  if (extraHeaders) {
    Object.entries(extraHeaders).forEach(([key, value]) => {
      finalHeaders.set(key, value)
    })
  }

  return finalHeaders
}

const processQueue = (error: Error | null, newToken: string | null): void => {
  failedQueue.forEach(({ resolve, reject, input, init }) => {
    if (error || !newToken) {
      reject(error)
      return
    }

    const retryInit: RequestInit = {
      ...init,
      headers: buildHeaders(init?.headers, newToken),
    }

    resolve(fetch(input, retryInit))
  })

  failedQueue = []
}

async function apiFetch(input: RequestInfo | URL, init?: RequestInit): Promise<Response> {
  const authStore = useAuthStore()

  const requestInit: RequestInit = {
    ...init,
    headers: buildHeaders(init?.headers, authStore.token || undefined),
  }

  const response = await fetch(input, requestInit)

  if (response.status !== 401) {
    return response
  }

  const clonedResponse = response.clone()
  let errorData: { error?: string } = {}

  try {
    errorData = await clonedResponse.json()
  } catch {
    authStore.clearAuthData()
    await router.push({ name: 'Signin' })
    return response
  }

  if (errorData.error === 'INVALID_TOKEN' || errorData.error === 'MISSING_TOKEN') {
    authStore.clearAuthData()
    await router.push({ name: 'Signin' })
    return response
  }

  if (errorData.error === 'TOKEN_EXPIRED') {
    if (isRefreshing) {
      return new Promise<Response>((resolve, reject) => {
        failedQueue.push({
          resolve,
          reject,
          input,
          init,
        })
      })
    }

    isRefreshing = true

    try {
      const refreshResponse = await fetch(`${API_URL}/refresh`, {
        method: 'POST',
        headers: buildHeaders(undefined, authStore.token || undefined, {
          Accept: 'application/json',
        }),
      })

      if (!refreshResponse.ok) {
        throw new Error('Refresh failed')
      }

      const refreshData: { access_token: string } = await refreshResponse.json()
      const newToken = refreshData.access_token

      if (!newToken) {
        throw new Error('No access token returned from refresh')
      }

      authStore.setAccessToken(newToken)

      processQueue(null, newToken)

      const retryInit: RequestInit = {
        ...init,
        headers: buildHeaders(init?.headers, newToken),
      }

      return await fetch(input, retryInit)
    } catch (refreshError) {
      processQueue(refreshError instanceof Error ? refreshError : new Error('Refresh failed'), null)
      authStore.clearAuthData()
      await router.push({ name: 'Signin' })
      return response
    } finally {
      isRefreshing = false
    }
  }

  authStore.clearAuthData()
  await router.push({ name: 'Signin' })
  return response
}

export default apiFetch