<script setup lang="ts">
import { useForm, useField } from 'vee-validate'
import * as yup from 'yup'
import { useAuthStore } from '@/stores/authStore'
import { isAxiosError } from 'axios'
import { useToast } from 'primevue/usetoast'

const authStore = useAuthStore()
const toast = useToast()

const schema = yup.object({
  email: yup.string().required('Email is required').email('Enter a valid email address'),
  password: yup.string().required('Password is required').min(6, 'Minimum 6 characters'),
})

const { handleSubmit, setErrors, isSubmitting } = useForm({ validationSchema: schema })
const { value: email, errorMessage: emailError } = useField('email')
const { value: password, errorMessage: passwordError } = useField('password')

const onSubmit = handleSubmit(async (values) => {
  try {
    await authStore.login({ email: values.email, password: values.password })
    toast.add({ severity: 'success', summary: 'Welcome back', detail: 'Login successful', life: 3000 })
  } catch (error) {
    if (isAxiosError(error) && error.response?.status === 422) {
      setErrors(error.response.data.errors ?? {})
    } else if (isAxiosError(error)) {
      const msg = error.response?.data?.message ?? 'Login failed'
      toast.add({ severity: 'error', summary: 'Error', detail: msg, life: 5000 })
    }
  }
})
</script>

<template>
  <div class="min-h-screen flex bg-slate-50">

    <!-- Left Panel — Branding -->
    <div class="hidden lg:flex lg:w-[480px] xl:w-[540px] bg-slate-900 flex-col justify-between p-12 relative overflow-hidden">
      <!-- Background pattern -->
      <div class="absolute inset-0 opacity-[0.03]"
        style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;" />

      <div class="relative">
        <div class="flex items-center gap-3 mb-16">
          <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center overflow-hidden">
            <img src="/logo.png" alt="Logo" class="w-9 h-9 object-contain" />
          </div>
          <span class="text-xl font-bold text-white">Integral Valet Management</span>
        </div>

        <h1 class="text-4xl font-bold text-white leading-tight mb-4">
          Parking management,<br />
          <span class="text-indigo-400">simplified.</span>
        </h1>
        <p class="text-slate-400 text-lg leading-relaxed max-w-sm">
          Multi-tenant valet and parking management platform. One system, unlimited clients.
        </p>
      </div>

      <div class="relative">
        <div class="flex items-center gap-8 text-sm text-slate-500">
          <div class="flex items-center gap-2">
            <i class="pi pi-shield text-indigo-400 text-xs" />
            <span>Secure</span>
          </div>
          <div class="flex items-center gap-2">
            <i class="pi pi-bolt text-indigo-400 text-xs" />
            <span>Fast</span>
          </div>
          <div class="flex items-center gap-2">
            <i class="pi pi-globe text-indigo-400 text-xs" />
            <span>Multi-tenant</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Panel — Login Form -->
    <div class="flex-1 flex items-center justify-center p-6 sm:p-12">
      <div class="w-full max-w-[400px]">

        <!-- Mobile logo -->
        <div class="flex items-center gap-3 mb-10 lg:hidden">
          <div class="w-9 h-9 rounded-lg bg-white border border-slate-200 flex items-center justify-center overflow-hidden">
            <img src="/logo.png" alt="Logo" class="w-8 h-8 object-contain" />
          </div>
          <span class="text-lg font-bold text-slate-900">Integral Valet Management</span>
        </div>

        <div class="mb-8">
          <h2 class="text-2xl font-bold text-slate-900">Sign in</h2>
          <p class="text-slate-500 mt-1.5 text-[15px]">Enter your credentials to access your account</p>
        </div>

        <form @submit.prevent="onSubmit" novalidate class="space-y-5">
          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">
              Email address
            </label>
            <input
              id="email"
              v-model="email"
              type="email"
              autocomplete="email"
              class="w-full h-11 border rounded-lg px-4 text-sm text-slate-900 placeholder:text-slate-400 outline-none transition-all"
              :class="emailError ? 'border-red-400 bg-red-50/50' : 'border-slate-300 bg-white hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10'"
              placeholder="you@company.com"
            />
            <p v-if="emailError" class="mt-1.5 text-[13px] text-red-500 flex items-center gap-1.5">
              <i class="pi pi-exclamation-circle text-[11px]" />
              {{ emailError }}
            </p>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
              Password
            </label>
            <input
              id="password"
              v-model="password"
              type="password"
              autocomplete="current-password"
              class="w-full h-11 border rounded-lg px-4 text-sm text-slate-900 placeholder:text-slate-400 outline-none transition-all"
              :class="passwordError ? 'border-red-400 bg-red-50/50' : 'border-slate-300 bg-white hover:border-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10'"
              placeholder="Enter your password"
            />
            <p v-if="passwordError" class="mt-1.5 text-[13px] text-red-500 flex items-center gap-1.5">
              <i class="pi pi-exclamation-circle text-[11px]" />
              {{ passwordError }}
            </p>
          </div>

          <!-- Submit -->
          <button
            type="submit"
            :disabled="isSubmitting"
            class="w-full h-11 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 shadow-sm shadow-indigo-600/20 hover:shadow-md hover:shadow-indigo-600/25"
          >
            <i v-if="isSubmitting" class="pi pi-spinner pi-spin text-sm" />
            <span>{{ isSubmitting ? 'Signing in...' : 'Sign in' }}</span>
          </button>
        </form>

        <p class="mt-8 text-center text-[13px] text-slate-400">
          Powered by Integral Valet Management &mdash; White Label Parking SaaS
        </p>
      </div>
    </div>
  </div>
</template>
