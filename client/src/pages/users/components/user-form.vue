<script lang="ts" setup>
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { z } from 'zod'

import { Button } from '@/components/ui/button'
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form'
import { Input } from '@/components/ui/input'
import { useToast } from '@/composables/use-toast'
import { useErrorStore } from '@/stores/error.store'
import { useCreateUserMutation } from '@/services/users.service'

const emits = defineEmits<{
  (e: 'close'): void
}>()

const toast = useToast()
const errorStore = useErrorStore()
const createUserMutation = useCreateUserMutation()

const formSchema = toTypedSchema(
  z
    .object({
      name: z.string().min(1, 'Name is required.'),
      email: z.string().email('Please enter a valid email address.').min(1, 'Email is required.'),
      password: z.string().min(8, 'Password must be at least 8 characters.'),
      password_confirmation: z.string().min(1, 'Please confirm your password.'),
      profile_photo: z.instanceof(File).optional().nullable(),
    })
    .refine((data) => data.password === data.password_confirmation, {
      message: 'Passwords do not match.',
      path: ['password_confirmation'],
    }),
)

const { handleSubmit, setFieldError, resetForm, setValue } = useForm({
  validationSchema: formSchema,
  initialValues: {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    profile_photo: null,
  },
})

const profilePhotoPreview = ref<string | null>(null)

function handlePhotoChange(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    // Validate file type
    if (!file.type.startsWith('image/')) {
      toast.showError('Please select an image file.')
      target.value = ''
      return
    }
    // Validate file size (2MB max)
    if (file.size > 2 * 1024 * 1024) {
      toast.showError('Image size must be less than 2MB.')
      target.value = ''
      return
    }
    // Set the file value in the form
    setValue('profile_photo', file)
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      profilePhotoPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
  else {
    setValue('profile_photo', null)
    profilePhotoPreview.value = null
  }
}

const onSubmit = handleSubmit(async (values) => {
  try {
    await createUserMutation.mutateAsync({
      name: values.name,
      email: values.email,
      password: values.password,
      password_confirmation: values.password_confirmation,
      profile_photo: values.profile_photo || null,
    })

    toast.showSuccess('User created successfully!')
    profilePhotoPreview.value = null
    resetForm()
    emits('close')
  }
  catch (error: any) {
    // Store error with context
    errorStore.setError(error, { context: 'createUser' })

    // Handle backend validation errors (422)
    if (error.response?.status === 422) {
      const backendErrors = error.response.data.errors || {}
      // Set field errors from backend response
      Object.keys(backendErrors).forEach((field) => {
        const fieldErrors = backendErrors[field]
        if (Array.isArray(fieldErrors) && fieldErrors.length > 0) {
          setFieldError(field as 'name' | 'email' | 'password' | 'password_confirmation' | 'profile_photo', fieldErrors[0])
        }
      })
    }

    // Use error store utilities for messages
    const message = errorStore.getErrorMessage(error)
    const validationErrors = errorStore.getValidationErrors(error)

    // Show toast with appropriate message
    if (Object.keys(validationErrors).length > 0) {
      const firstError = Object.values(validationErrors)[0]?.[0]
      toast.showError(firstError || message)
    }
    else {
      toast.showError(message)
    }
  }
})
</script>

<template>
  <form class="space-y-4" @submit="onSubmit">
    <FormField v-slot="{ componentField }" name="name">
      <FormItem>
        <FormLabel>Name</FormLabel>
        <FormControl>
          <Input type="text" v-bind="componentField" placeholder="John Doe" />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="email">
      <FormItem>
        <FormLabel>Email address</FormLabel>
        <FormControl>
          <Input type="email" v-bind="componentField" placeholder="john@example.com" />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="password">
      <FormItem>
        <FormLabel>Password</FormLabel>
        <FormControl>
          <Input type="password" v-bind="componentField" placeholder="********" />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="password_confirmation">
      <FormItem>
        <FormLabel>Confirm Password</FormLabel>
        <FormControl>
          <Input type="password" v-bind="componentField" placeholder="********" />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="profile_photo">
      <FormItem>
        <FormLabel>Profile Photo</FormLabel>
        <FormControl>
          <div class="space-y-2">
            <Input
              type="file"
              accept="image/*"
              @change="handlePhotoChange"
            />
            <p class="text-xs text-muted-foreground">
              Upload a profile photo (max 2MB). Accepted formats: JPG, PNG, GIF, etc.
            </p>
            <div v-if="profilePhotoPreview" class="mt-2">
              <img
                :src="profilePhotoPreview"
                alt="Profile photo preview"
                class="h-24 w-24 rounded-full object-cover border"
              />
            </div>
          </div>
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <Button type="submit" class="w-full" :disabled="createUserMutation.isPending.value">
      <UiSpinner v-if="createUserMutation.isPending.value" class="mr-2" />
      Create User
    </Button>
  </form>
</template>
