import axios from 'axios'
import type { AxiosError, InternalAxiosRequestConfig, AxiosResponse } from 'axios'
import type { IApiErrorResponse } from '@/types'
import router from '@/router'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api/v1',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

// Request interceptor
api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    // Inject X-Tenant-ID for building owners (stored in sessionStorage, cleared on tab close)
    const activeTenantId = sessionStorage.getItem('activeTenantId')
    if (activeTenantId) {
      config.headers['X-Tenant-ID'] = activeTenantId
    }

    if (import.meta.env.DEV) {
      console.debug(`[API] ${config.method?.toUpperCase()} ${config.url}`)
    }

    return config
  },
  (error) => Promise.reject(error),
)

// Response interceptor
api.interceptors.response.use(
  (response: AxiosResponse) => {
    return response.data
  },
  (error: AxiosError<IApiErrorResponse>) => {
    const status = error.response?.status

    switch (status) {
      case 401:
        localStorage.removeItem('token')
        router.push({ name: 'auth.login', query: { expired: '1' } })
        break

      case 402:
        // Plan limit reached — let caller handle
        break

      case 422:
        // Let it through — handled by VeeValidate setErrors
        break

      case 403:
      case 404:
      case 409:
      case 429:
      case 500:
      case 502:
      case 503:
        break

      default:
        if (!error.response) {
          console.error('[API] Network error')
        }
    }

    return Promise.reject(error)
  },
)

export default api
