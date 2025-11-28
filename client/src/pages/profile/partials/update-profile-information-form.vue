<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'
import { toast } from 'vue-sonner'

import FormSection from '@/components/form-section.vue'
import InputError from '@/components/input-error.vue'
import { useAuthStore } from '@/stores/auth'
import { useUpdateUserMutation } from '@/services/api/user.api'
import type { User } from '@/services/api/auth.api'

const props = defineProps<{
  user: User | null
}>()

const authStore = useAuthStore()

const schema = toTypedSchema(z.object({
  name: z.string().min(1, 'Name is required'),
  email: z.string().email('Invalid email address'),
}))

const { handleSubmit, defineField, errors } = useForm({
  validationSchema: schema,
  initialValues: {
    name: props.user?.name || '',
    email: props.user?.email || '',
  },
})

const [name] = defineField('name')
const [email] = defineField('email')

const updateUserMutation = useUpdateUserMutation(props.user?.id || 0)
const processing = computed(() => updateUserMutation.isPending.value)

const onSubmit = handleSubmit(async (values) => {
  if (!props.user?.id)
    return

  try {
    await updateUserMutation.mutateAsync({
      name: values.name,
      email: values.email,
    })
    toast.success('Profile information updated')
    await authStore.fetchUser()
  }
  catch (error: any) {
    const errorData = error.response?.data
    if (errorData?.errors) {
      // Handle validation errors
      Object.keys(errorData.errors).forEach((key) => {
        errors.value[key] = Array.isArray(errorData.errors[key])
          ? errorData.errors[key][0]
          : errorData.errors[key]
      })
    }
    else {
      toast.error(errorData?.message || 'Failed to update profile information')
    }
  }
})
</script>

<template>
  <FormSection @submitted="onSubmit">
    <template #title>
      Profile Information
    </template>

    <template #description>
      Update your account's profile information and email address.
    </template>

    <template #form>
      <!-- Name -->
      <div class="col-span-6 sm:col-span-4">
        <UiLabel for="name">Name</UiLabel>
        <UiInput
          id="name"
          v-model="name"
          type="text"
          class="mt-1 block w-full"
          required
          autocomplete="name"
        />
        <InputError :message="errors.name" class="mt-2" />
      </div>

      <!-- Email -->
      <div class="col-span-6 sm:col-span-4">
        <UiLabel for="email">Email</UiLabel>
        <UiInput
          id="email"
          v-model="email"
          type="email"
          class="mt-1 block w-full"
          required
          autocomplete="username"
          disabled
        />
        <InputError :message="errors.email" class="mt-2" />
        <p class="mt-2 text-sm text-muted-foreground">
          Email cannot be changed. Contact support if you need to update your email.
        </p>
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

