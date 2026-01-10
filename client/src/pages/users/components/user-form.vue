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
import { handleFileUpload } from '@/utils/file'
import { convertToFormData, setFormFieldErrors } from '@/utils/form'

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

const { handleSubmit, setFieldError, resetForm } = form

const profilePhotoPreview = ref<string | null>(null)
const existingProfilePhotoUrl = computed(
  () => props.user?.profile_photo_url || null,
)

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
): IUpdateUserRequest | ICreateUserRequest | FormData {
  const data: IUpdateUserRequest | ICreateUserRequest =
    isEdit && props.user
      ? {
          id: props.user.id,
          ...values,
          role: values.role || props.user.roles?.[0]?.name || '',
        }
      : {
          name: values.name,
          email: values.email,
          password: values.password!,
          password_confirmation: values.password_confirmation!,
          profile_photo: values.profile_photo || null,
          role: values.role || EUserRole.USER,
          status: EUserStatus.REGISTERED,
        }

  // Convert to FormData if file is present
  // For create mode, exclude id; for update mode, id is already in data
  return convertToFormData(data, {
    excludeId: !isEdit,
  })
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
