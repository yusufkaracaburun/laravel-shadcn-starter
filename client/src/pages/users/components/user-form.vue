<script lang="ts" setup>
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { z } from 'zod'

import type { User } from '@/services/users.service'

import { Button } from '@/components/ui/button'
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form'
import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { useToast } from '@/composables/use-toast'
import {
  useCreateUserMutation,
  useGetRolesQuery,
  useUpdateUserMutation,
} from '@/services/users.service'
import { useErrorStore } from '@/stores/error.store'

const props = defineProps<{
  user?: User | null
}>()

const emits = defineEmits<{
  close: []
}>()

const toast = useToast()
const errorStore = useErrorStore()
const createUserMutation = useCreateUserMutation()
const updateUserMutation = useUpdateUserMutation()

// Fetch available roles
const { data: rolesResponse } = useGetRolesQuery()
const roles = computed(() => rolesResponse.value?.data ?? [])

const isEditMode = computed(() => !!props.user)

// Dynamic schema based on edit mode
const formSchema = computed(() => {
  const baseSchema = z.object({
    name: z.string().min(1, 'Name is required.'),
    email: z.string().email('Please enter a valid email address.').min(1, 'Email is required.'),
    profile_photo: z.instanceof(File).optional().nullable(),
    role: z.string().optional().nullable(),
  })

  if (isEditMode.value) {
    // In edit mode, password is optional
    return baseSchema
      .extend({
        password: z.string().min(8, 'Password must be at least 8 characters.').optional(),
        password_confirmation: z.string().min(1, 'Please confirm your password.').optional(),
      })
      .refine(
        (data) => {
          // Only validate password match if password is provided
          if (data.password || data.password_confirmation) {
            return data.password === data.password_confirmation
          }
          return true
        },
        {
          message: 'Passwords do not match.',
          path: ['password_confirmation'],
        },
      )
  }

  // In create mode, password is required
  return baseSchema
    .extend({
      password: z.string().min(8, 'Password must be at least 8 characters.'),
      password_confirmation: z.string().min(1, 'Please confirm your password.'),
    })
    .refine((data) => data.password === data.password_confirmation, {
      message: 'Passwords do not match.',
      path: ['password_confirmation'],
    })
})

function getInitialValues() {
  return {
    name: props.user?.name || '',
    email: props.user?.email || '',
    password: '',
    password_confirmation: '',
    profile_photo: null,
    role: props.user?.roles?.[0]?.name || null,
  }
}

const form = useForm({
  validationSchema: computed(() => toTypedSchema(formSchema.value)),
  initialValues: getInitialValues(),
})

const { handleSubmit, setFieldError, resetForm } = form

const profilePhotoPreview = ref<string | null>(null)
const existingProfilePhotoUrl = computed(() => props.user?.profile_photo_url || null)

// Watch for user changes to update form values and preview
watch(
  () => props.user,
  (user) => {
    if (user) {
      // Use resetForm with values to update all fields at once
      resetForm({
        values: {
          name: user.name || '',
          email: user.email || '',
          password: '',
          password_confirmation: '',
          profile_photo: null,
          role: user.roles?.[0]?.name || null,
        },
      })
      if (user.profile_photo_url) {
        profilePhotoPreview.value = user.profile_photo_url
      }
    } else {
      resetForm({
        values: getInitialValues(),
      })
      profilePhotoPreview.value = null
    }
  },
  { immediate: true },
)

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
    // Set the file value in the form using setFieldValue
    form.setFieldValue('profile_photo', file)
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      profilePhotoPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  } else {
    form.setFieldValue('profile_photo', null)
    profilePhotoPreview.value = null
  }
}

const onSubmit = handleSubmit(async (values) => {
  try {
    if (isEditMode.value && props.user) {
      // Update existing user
      const updateData: Record<string, any> = {
        name: values.name || '',
        email: values.email || '',
      }

      // Only include password if provided
      if (values.password) {
        updateData.password = values.password
        updateData.password_confirmation = values.password_confirmation
      }

      // Only include profile_photo if a new file was selected
      if (values.profile_photo) {
        updateData.profile_photo = values.profile_photo
      }

      // Include role if provided (can be empty string to clear role)
      if (values.role !== null && values.role !== undefined) {
        updateData.role = values.role || null
      }

      await updateUserMutation.mutateAsync({
        userId: props.user.id,
        data: updateData,
      })

      toast.showSuccess('User updated successfully!')
    } else {
      // Create new user
      await createUserMutation.mutateAsync({
        name: values.name || '',
        email: values.email || '',
        password: values.password || '',
        password_confirmation: values.password_confirmation || '',
        profile_photo: values.profile_photo || null,
        role: values.role || null,
      })

      toast.showSuccess('User created successfully!')
    }

    profilePhotoPreview.value = null
    resetForm()
    emits('close')
  } catch (error: any) {
    // Store error with context
    const context = isEditMode.value ? 'updateUser' : 'createUser'
    errorStore.setError(error, { context })

    // Handle backend validation errors (422)
    if (error.response?.status === 422) {
      const backendErrors = error.response.data.errors || {}
      // Set field errors from backend response
      Object.keys(backendErrors).forEach((field) => {
        const fieldErrors = backendErrors[field]
        if (Array.isArray(fieldErrors) && fieldErrors.length > 0) {
          setFieldError(
            field as
              | 'name'
              | 'email'
              | 'password'
              | 'password_confirmation'
              | 'profile_photo'
              | 'role',
            fieldErrors[0],
          )
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
    } else {
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
          <Input
            type="password"
            v-bind="componentField"
            :placeholder="isEditMode ? 'Leave blank to keep current password' : '********'"
          />
        </FormControl>
        <FormMessage />
        <p v-if="isEditMode" class="text-xs text-muted-foreground mt-1">
          Leave blank to keep the current password
        </p>
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="password_confirmation">
      <FormItem>
        <FormLabel>Confirm Password</FormLabel>
        <FormControl>
          <Input
            type="password"
            v-bind="componentField"
            :placeholder="isEditMode ? 'Leave blank to keep current password' : '********'"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="role">
      <FormItem>
        <FormLabel>Role</FormLabel>
        <FormControl>
          <Select v-bind="componentField">
            <SelectTrigger>
              <SelectValue placeholder="Select a role (optional)" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="role in roles" :key="role.id" :value="role.name">
                {{ role.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </FormControl>
        <FormMessage />
        <p class="text-xs text-muted-foreground mt-1">Optional: Assign a role to this user</p>
      </FormItem>
    </FormField>

    <FormField name="profile_photo">
      <FormItem>
        <FormLabel>Profile Photo</FormLabel>
        <FormControl>
          <div class="space-y-2">
            <Input type="file" accept="image/*" @change="handlePhotoChange" />
            <p class="text-xs text-muted-foreground">
              Upload a profile photo (max 2MB). Accepted formats: JPG, PNG, GIF, etc.
            </p>
            <div v-if="profilePhotoPreview || existingProfilePhotoUrl" class="mt-2">
              <img
                :src="(profilePhotoPreview || existingProfilePhotoUrl) ?? ''"
                alt="Profile preview"
                class="h-24 w-24 rounded-full object-cover border"
              />
            </div>
          </div>
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <Button
      type="submit"
      class="w-full"
      :disabled="
        isEditMode ? updateUserMutation.isPending.value : createUserMutation.isPending.value
      "
    >
      <UiSpinner
        v-if="isEditMode ? updateUserMutation.isPending.value : createUserMutation.isPending.value"
        class="mr-2"
      />
      {{ isEditMode ? 'Update User' : 'Create User' }}
    </Button>
  </form>
</template>
