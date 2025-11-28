<script setup lang="ts">
import axios from 'axios'
import { ref } from 'vue'

import InputError from './input-error.vue'
import env from '@/utils/env'
import { getCsrfCookie } from '@/services/api/auth.api'

interface Props {
  title?: string
  content?: string
  button?: string
}

withDefaults(defineProps<Props>(), {
  title: 'Confirm Password',
  content: 'For your security, please confirm your password to continue.',
  button: 'Confirm',
})

const emit = defineEmits<{
  (e: 'confirmed', password: string): void
}>()

const confirmingPassword = ref(false)
const password = ref('')
const error = ref<string | null>(null)
const processing = ref(false)

const passwordInput = ref<HTMLInputElement | null>(null)

async function confirmPassword() {
  if (!password.value) {
    error.value = 'Password is required'
    return
  }

  processing.value = true
  error.value = null

  try {
    // Get CSRF cookie first
    await getCsrfCookie()

    // Confirm password - Fortify route
    const baseURL = env.VITE_SERVER_API_URL
    await axios.post('/user/confirm-password', {
      password: password.value,
    }, {
      baseURL,
      withCredentials: true,
    })

    processing.value = false
    emit('confirmed', password.value)
    closeModal()
  }
  catch (err: any) {
    processing.value = false
    const errorData = err.response?.data
    if (errorData?.errors?.password) {
      error.value = Array.isArray(errorData.errors.password)
        ? errorData.errors.password[0]
        : errorData.errors.password
    }
    else if (errorData?.message) {
      error.value = errorData.message
    }
    else {
      error.value = 'Password confirmation failed'
    }

    // Focus password input
    passwordInput.value?.focus()
  }
}

function closeModal() {
  confirmingPassword.value = false
  password.value = ''
  error.value = null
}
</script>

<template>
  <span>
    <UiDialog v-model:open="confirmingPassword">
      <UiDialogTrigger as-child>
        <slot />
      </UiDialogTrigger>
      <UiDialogContent>
        <UiDialogHeader>
          <UiDialogTitle>{{ title }}</UiDialogTitle>
          <UiDialogDescription>
            {{ content }}
          </UiDialogDescription>
        </UiDialogHeader>

        <div class="mt-4">
          <UiInput
            ref="passwordInput"
            v-model="password"
            type="password"
            class="mt-1 block w-full"
            placeholder="Password"
            autocomplete="current-password"
            @keyup.enter="confirmPassword"
          />

          <InputError :message="error" class="mt-2" />
        </div>

        <UiDialogFooter class="mt-4">
          <UiDialogClose as-child>
            <UiButton variant="secondary" @click="closeModal">
              Cancel
            </UiButton>
          </UiDialogClose>

          <UiButton
            variant="destructive"
            class="ms-3"
            :class="{ 'opacity-25': processing }"
            :disabled="processing"
            @click="confirmPassword"
          >
            {{ button }}
          </UiButton>
        </UiDialogFooter>
      </UiDialogContent>
    </UiDialog>
  </span>
</template>

