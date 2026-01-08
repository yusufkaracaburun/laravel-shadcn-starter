<script lang="ts" setup>
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'

import { Button } from '@/components/ui/button'
import {
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/components/ui/form'
import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { useToast } from '@/composables/use-toast'
import { useUsers } from '@/composables/use-users'
import { useErrorStore } from '@/stores/error.store'

import type {
  ICreateUserRequest,
  IUpdateUserRequest,
  IUser,
} from '../models/users'

import { createUserFormSchema, editUserFormSchema } from '../data/schema'
import { EUserRole, EUserStatus } from '../models/users'

const props = defineProps<{
  user?: IUser | null
}>()

const emits = defineEmits<{
  close: []
}>()

const toast = useToast()
const errorStore = useErrorStore()
const {
  userPrerequisitesResponse,
  createUser,
  updateUser,
  isCreating,
  isUpdating,
} = useUsers()

const roles = computed(() => userPrerequisitesResponse.value?.roles ?? [])

const isEditMode = computed(() => !!props.user)

const formSchema = computed(() => {
  return isEditMode.value ? editUserFormSchema : createUserFormSchema
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
const existingProfilePhotoUrl = computed(
  () => props.user?.profile_photo_url || null,
)

watch(
  () => props.user,
  (user) => {
    if (user) {
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
    if (!file.type.startsWith('image/')) {
      toast.showError('Please select an image file.')
      target.value = ''
      return
    }
    if (file.size > 2 * 1024 * 1024) {
      toast.showError('Image size must be less than 2MB.')
      target.value = ''
      return
    }
    form.setFieldValue('profile_photo', file)
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

// Helper function to convert role name to enum value
function convertRoleToEnum(roleName: string | null | undefined): EUserRole {
  if (!roleName) {
    return EUserRole.USER
  }
  const roleEnum = Object.values(EUserRole).find((r) => r === roleName)
  return roleEnum || EUserRole.USER
}

// Helper function to handle validation errors from backend
function handleValidationErrors(error: any) {
  if (error.response?.status === 422) {
    const backendErrors = error.response.data.errors || {}
    const validFields = [
      'name',
      'email',
      'password',
      'password_confirmation',
      'profile_photo',
      'role',
    ] as const

    Object.keys(backendErrors).forEach((field) => {
      const fieldErrors = backendErrors[field]
      if (
        Array.isArray(fieldErrors) &&
        fieldErrors.length > 0 &&
        validFields.includes(field as (typeof validFields)[number])
      ) {
        setFieldError(field as (typeof validFields)[number], fieldErrors[0])
      }
    })
  }
}

// Helper function to show error toast
function showErrorToast(error: any) {
  const message = errorStore.getErrorMessage(error)
  const validationErrors = errorStore.getValidationErrors(error)

  if (Object.keys(validationErrors).length > 0) {
    const firstError = Object.values(validationErrors)[0]?.[0]
    toast.showError(firstError || message)
  } else {
    toast.showError(message)
  }
}

const onSubmit = handleSubmit(async (values) => {
  try {
    if (isEditMode.value && props.user) {
      // Update existing user - only include fields that have values
      const updateData: Partial<IUpdateUserRequest> = {
        id: props.user.id,
      }

      // Only include fields that are provided and different from current values
      if (values.name && values.name !== props.user.name) {
        updateData.name = values.name
      }
      if (values.email && values.email !== props.user.email) {
        updateData.email = values.email
      }
      // Password fields - only include if provided
      if (values.password) {
        updateData.password = values.password
        updateData.password_confirmation = values.password_confirmation
      }
      // Profile photo - include if a new file was selected
      if (values.profile_photo) {
        updateData.profile_photo = values.profile_photo
      }
      // Role - convert and include if provided and different
      if (values.role) {
        const roleEnum = convertRoleToEnum(values.role)
        const currentRoleName = props.user.roles?.[0]?.name
        // Only update if the role name has changed
        if (values.role !== currentRoleName) {
          updateData.role = roleEnum
        }
      }

      await updateUser(props.user.id, updateData as IUpdateUserRequest)
    } else {
      // Create new user
      const createData: ICreateUserRequest = {
        name: values.name,
        email: values.email,
        password: values.password!,
        password_confirmation: values.password_confirmation!,
        profile_photo: values.profile_photo || null,
        role: convertRoleToEnum(values.role),
        status: EUserStatus.REGISTERED,
      }

      await createUser(createData)
    }

    profilePhotoPreview.value = null
    resetForm()
    emits('close')
  } catch (error: any) {
    const context = isEditMode.value ? 'updateUser' : 'createUser'
    errorStore.setError(error, { context })

    handleValidationErrors(error)
    showErrorToast(error)
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
          <Input
            type="email"
            v-bind="componentField"
            placeholder="john@example.com"
          />
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
            :placeholder="
              isEditMode ? 'Leave blank to keep current password' : '********'
            "
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
            :placeholder="
              isEditMode ? 'Leave blank to keep current password' : '********'
            "
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
              <SelectItem
                v-for="role in roles"
                :key="role.id"
                :value="role.name"
              >
                {{ role.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </FormControl>
        <FormMessage />
        <p class="text-xs text-muted-foreground mt-1">
          Optional: Assign a role to this user
        </p>
      </FormItem>
    </FormField>

    <FormField name="profile_photo">
      <FormItem>
        <FormLabel>Profile Photo</FormLabel>
        <FormControl>
          <div class="space-y-2">
            <Input type="file" accept="image/*" @change="handlePhotoChange" />
            <p class="text-xs text-muted-foreground">
              Upload a profile photo (max 2MB). Accepted formats: JPG, PNG, GIF,
              etc.
            </p>
            <div
              v-if="profilePhotoPreview || existingProfilePhotoUrl"
              class="mt-2"
            >
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
      :disabled="isEditMode ? isUpdating : isCreating"
    >
      <UiSpinner v-if="isEditMode ? isUpdating : isCreating" class="mr-2" />
      {{ isEditMode ? 'Update User' : 'Create User' }}
    </Button>
  </form>
</template>
