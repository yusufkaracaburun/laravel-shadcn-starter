<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'
import { toast } from 'vue-sonner'

import FormSection from '@/components/form-section.vue'
import InputError from '@/components/input-error.vue'

const schema = toTypedSchema(z.object({
  current_password: z.string().min(1, 'Current password is required'),
  password: z.string().min(8, 'Password must be at least 8 characters'),
  password_confirmation: z.string().min(1, 'Password confirmation is required'),
}).refine((data) => data.password === data.password_confirmation, {
  message: 'Passwords do not match',
  path: ['password_confirmation'],
}))

const { handleSubmit, defineField, errors, resetForm } = useForm({
  validationSchema: schema,
  initialValues: {
    current_password: '',
    password: '',
    password_confirmation: '',
  },
})

const [currentPassword] = defineField('current_password')
const [password] = defineField('password')
const [passwordConfirmation] = defineField('password_confirmation')

const processing = ref(false)
const passwordInput = ref<HTMLInputElement | null>(null)
const currentPasswordInput = ref<HTMLInputElement | null>(null)

const onSubmit = handleSubmit(async (values) => {
  processing.value = true

  try {
    // Note: Password update endpoint may need to be created in backend
    // This is a placeholder - adapt to your backend API
    const { useAxios } = await import('@/composables/use-axios')
    const { axiosInstance } = useAxios()
    const { getCsrfCookie } = await import('@/services/api/auth.api')
    const env = (await import('@/utils/env')).default

    await getCsrfCookie()

    const baseURL = env.VITE_SERVER_API_URL
    await axiosInstance.put('/user/password', {
      current_password: values.current_password,
      password: values.password,
      password_confirmation: values.password_confirmation,
    }, {
      baseURL,
    })

    resetForm()
    toast.success('Password updated')
  }
  catch (error: any) {
    const errorData = error.response?.data
    if (errorData?.errors) {
      Object.keys(errorData.errors).forEach((key) => {
        errors.value[key] = Array.isArray(errorData.errors[key])
          ? errorData.errors[key][0]
          : errorData.errors[key]
      })

      if (errorData.errors.password) {
        resetForm({ values: { password: '', password_confirmation: '' } })
        passwordInput.value?.focus()
      }

      if (errorData.errors.current_password) {
        resetForm({ values: { current_password: '' } })
        currentPasswordInput.value?.focus()
      }
    }
    else {
      toast.error(errorData?.message || 'Failed to update password')
    }
  }
  finally {
    processing.value = false
  }
})
</script>

<template>
  <FormSection @submitted="onSubmit">
    <template #title>
      Update Password
    </template>

    <template #description>
      Ensure your account is using a long, random password to stay secure.
    </template>

    <template #form>
      <div class="col-span-6 sm:col-span-4">
        <UiLabel for="current_password">Current Password</UiLabel>
        <UiInput
          id="current_password"
          ref="currentPasswordInput"
          v-model="currentPassword"
          type="password"
          class="mt-1 block w-full"
          autocomplete="current-password"
        />
        <InputError :message="errors.current_password" class="mt-2" />
      </div>

      <div class="col-span-6 sm:col-span-4">
        <UiLabel for="password">New Password</UiLabel>
        <UiInput
          id="password"
          ref="passwordInput"
          v-model="password"
          type="password"
          class="mt-1 block w-full"
          autocomplete="new-password"
        />
        <InputError :message="errors.password" class="mt-2" />
      </div>

      <div class="col-span-6 sm:col-span-4">
        <UiLabel for="password_confirmation">Confirm Password</UiLabel>
        <UiInput
          id="password_confirmation"
          v-model="passwordConfirmation"
          type="password"
          class="mt-1 block w-full"
          autocomplete="new-password"
        />
        <InputError
          :message="errors.password_confirmation"
          class="mt-2"
        />
      </div>
    </template>

    <template #actions>
      <UiButton
        :class="{ 'opacity-25': processing }"
        :disabled="processing"
      >
        Save
      </UiButton>
    </template>
  </FormSection>
</template>

