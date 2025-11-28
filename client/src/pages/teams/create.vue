<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import { z } from 'zod'
import { toast } from 'vue-sonner'
import { useRouter } from 'vue-router'
import axios from 'axios'

import FormSection from '@/components/form-section.vue'
import InputError from '@/components/input-error.vue'

const router = useRouter()

const schema = toTypedSchema(z.object({
  name: z.string().min(1, 'Team name is required'),
}))

const { handleSubmit, defineField, errors } = useForm({
  validationSchema: schema,
  initialValues: {
    name: '',
  },
})

const [name] = defineField('name')

const processing = ref(false)

const onSubmit = handleSubmit(async (values) => {
  processing.value = true

  try {
    // Note: Team creation endpoint may need to be created in backend
    // This is a placeholder - adapt to your backend API
    const { useAxios } = await import('@/composables/use-axios')
    const { axiosInstance } = useAxios()
    const { getCsrfCookie } = await import('@/services/api/auth.api')
    const env = (await import('@/utils/env')).default

    await getCsrfCookie()

    const baseURL = env.VITE_SERVER_API_URL
    const response = await axiosInstance.post('/teams', {
      name: values.name,
    }, {
      baseURL,
    })

    toast.success('Team created successfully')
    router.push({ name: 'teams.show', params: { team: response.data.data.id } })
  }
  catch (error: any) {
    const errorData = error.response?.data
    if (errorData?.errors) {
      Object.keys(errorData.errors).forEach((key) => {
        errors.value[key] = Array.isArray(errorData.errors[key])
          ? errorData.errors[key][0]
          : errorData.errors[key]
      })
    }
    else {
      toast.error(errorData?.message || 'Failed to create team')
    }
  }
  finally {
    processing.value = false
  }
})
</script>

<template>
  <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="mb-6">
      <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
        Create Team
      </h2>
    </div>

    <FormSection @submitted="onSubmit">
      <template #title>
        Team Details
      </template>

      <template #description>
        Create a new team to collaborate with others.
      </template>

      <template #form>
        <div class="col-span-6 sm:col-span-4">
          <UiLabel for="name">Team Name</UiLabel>
          <UiInput
            id="name"
            v-model="name"
            type="text"
            class="mt-1 block w-full"
            required
            autocomplete="organization"
          />
          <InputError :message="errors.name" class="mt-2" />
        </div>
      </template>

      <template #actions>
        <UiButton
          :class="{ 'opacity-25': processing }"
          :disabled="processing"
        >
          Create Team
        </UiButton>
      </template>
    </FormSection>
  </div>
</template>

