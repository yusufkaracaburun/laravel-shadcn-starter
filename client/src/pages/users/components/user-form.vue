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
import { XIcon } from '@/composables/use-icons.composable'
import { useToast } from '@/composables/use-toast.composable'
import { useUsers } from '@/composables/use-users.composable'
import { handleFileUpload } from '@/utils/file'
import { setFormFieldErrors } from '@/utils/form'

import type {
  ICreateUserRequest,
  IUpdateUserRequest,
  IUser,
} from '../models/users'

import { createUserFormSchema, editUserFormSchema } from '../data/schema'

const props = defineProps<{
  user?: IUser | null
}>()

const emits = defineEmits<{
  close: []
}>()

const toast = useToast()
const {
  userPrerequisitesResponse,
  createUser,
  updateUser,
  isCreating,
  isUpdating,
  getUserFormInitialValues,
} = useUsers()

const roles = computed(() => userPrerequisitesResponse.value?.roles ?? [])
const isEditMode = computed(() => !!props.user)
const formSchema = computed(() =>
  isEditMode.value ? editUserFormSchema : createUserFormSchema,
)

const initialValues = computed(() => getUserFormInitialValues(props.user))

const form = useForm({
  validationSchema: computed(() => toTypedSchema(formSchema.value)),
  initialValues: initialValues.value,
})

const { handleSubmit, setFieldError, resetForm, values } = form

const profilePhotoPreview = ref<string | null>(null)
const existingProfilePhotoUrl = computed(
  () => props.user?.profile_photo_url || null,
)
const fileInputRef = ref<HTMLInputElement | null>(null)

watch(
  () => props.user,
  (user) => {
    resetForm({ values: initialValues.value })
    profilePhotoPreview.value = user?.profile_photo_url || null
  },
  { immediate: true },
)

function handlePhotoChange(event: Event) {
  handleFileUpload(event, {
    fieldName: 'profile_photo',
    setFieldValue: form.setFieldValue,
    onPreview: (preview: string | null) => {
      profilePhotoPreview.value = preview
    },
    allowedTypes: ['image/*'],
    onError: (message: string) => {
      toast.showError(message)
    },
  })
}

function resetImage() {
  form.setFieldValue('profile_photo', null)
  profilePhotoPreview.value = null
  if (fileInputRef.value) {
    fileInputRef.value.value = ''
  }
}

const validFields = [
  'name',
  'email',
  'password',
  'password_confirmation',
  'profile_photo',
  'role',
] as const

function prepareRequestData(
  values: any,
  isEdit: boolean,
): IUpdateUserRequest | ICreateUserRequest {
  if (isEdit && props.user) {
    return {
      id: props.user.id,
      ...values,
      role: values.role,
    }
  }

  return {
    name: values.name,
    email: values.email,
    password: values.password!,
    password_confirmation: values.password_confirmation!,
    profile_photo: values.profile_photo || null,
    role: values.role,
    status: values.status,
  }
}

const onSubmit = handleSubmit(async (values) => {
  try {
    const requestData = prepareRequestData(values, isEditMode.value)

    if (isEditMode.value && props.user) {
      await updateUser(props.user.id, requestData as IUpdateUserRequest)
    } else {
      await createUser(requestData as ICreateUserRequest)
    }

    profilePhotoPreview.value = null
    resetForm()
    emits('close')
  } catch (error: any) {
    setFormFieldErrors(error, setFieldError, validFields)
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
              <SelectValue placeholder="Select a role" />
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
      </FormItem>
    </FormField>

    <FormField name="profile_photo">
      <FormItem>
        <FormLabel>Profile Photo</FormLabel>
        <FormControl>
          <div class="space-y-2">
            <Input
              ref="fileInputRef"
              type="file"
              accept="image/*"
              @change="handlePhotoChange"
            />
            <p class="text-xs text-muted-foreground">
              Upload a profile photo (max 2MB). Accepted formats: JPG, PNG, GIF,
              etc.
            </p>
            <div
              v-if="profilePhotoPreview || existingProfilePhotoUrl"
              class="mt-2 relative inline-block"
            >
              <img
                :src="(profilePhotoPreview || existingProfilePhotoUrl) ?? ''"
                alt="Profile preview"
                class="h-24 w-24 rounded-full object-cover border"
              />
              <button
                type="button"
                class="absolute -top-1 -right-1 rounded-full bg-destructive text-white p-1 hover:bg-destructive/90 transition-colors"
                :aria-label="
                  isEditMode ? 'Remove profile photo' : 'Clear selected image'
                "
                @click="resetImage"
              >
                <XIcon class="h-3 w-3" />
              </button>
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
  <pre>{{ values }}</pre>
</template>
