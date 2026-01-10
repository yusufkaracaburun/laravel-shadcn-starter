<script lang="ts" setup>
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { z } from 'zod'

import type { Company } from '@/services/companies.service'

import { Button } from '@/components/ui/button'
import {
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/components/ui/form'
import { Input } from '@/components/ui/input'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { useToast } from '@/composables/use-toast.composable'
import {
  useCreateCompanyMutation,
  useUpdateCompanyMutation,
} from '@/services/companies.service'
import { useErrorStore } from '@/stores/error.store'

import { employeeSizes, industries, statuses } from '../data/data'

const props = defineProps<{
  company?: Company | null
}>()

const emits = defineEmits<{
  close: []
}>()

const toast = useToast()
const errorStore = useErrorStore()
const createCompanyMutation = useCreateCompanyMutation()
const updateCompanyMutation = useUpdateCompanyMutation()

const isEditMode = computed(() => !!props.company)

// Dynamic schema based on edit mode
const formSchema = computed(() => {
  return z.object({
    name: z
      .string()
      .min(1, 'Name is required.')
      .max(255, 'Name must not exceed 255 characters.'),
    email: z
      .string()
      .email('Please enter a valid email address.')
      .min(1, 'Email is required.'),
    phone: z.string().optional().nullable(),
    industry: z
      .string()
      .min(1, 'Industry is required.')
      .refine(
        val =>
          [
            'technology',
            'finance',
            'healthcare',
            'retail',
            'manufacturing',
            'education',
          ].includes(val),
        'Please select a valid industry.',
      ),
    status: z
      .string()
      .min(1, 'Status is required.')
      .refine(
        val => ['active', 'inactive', 'pending'].includes(val),
        'Please select a valid status.',
      ),
    employees: z
      .string()
      .min(1, 'Employee size is required.')
      .refine(
        val => ['1-10', '11-50', '51-200', '201-500', '500+'].includes(val),
        'Please select a valid employee size.',
      ),
  })
})

function getInitialValues() {
  return {
    name: props.company?.name || '',
    email: props.company?.email || '',
    phone: props.company?.phone || null,
    industry: props.company?.industry || '',
    status: props.company?.status || '',
    employees: props.company?.employees || '',
  }
}

const form = useForm({
  validationSchema: computed(() => toTypedSchema(formSchema.value)),
  initialValues: getInitialValues(),
})

const { handleSubmit, setFieldError, resetForm } = form

// Watch for company changes to update form values
watch(
  () => props.company,
  (company) => {
    if (company) {
      resetForm({
        values: {
          name: company.name || '',
          email: company.email || '',
          phone: company.phone || null,
          industry: company.industry || '',
          status: company.status || '',
          employees: company.employees || '',
        },
      })
    } else {
      resetForm({
        values: getInitialValues(),
      })
    }
  },
  { immediate: true },
)

const onSubmit = handleSubmit(async (values) => {
  try {
    if (isEditMode.value && props.company) {
      // Update existing company
      const updateData: Record<string, any> = {
        name: values.name || '',
        email: values.email || '',
        phone: values.phone || null,
        industry: values.industry || '',
        status: values.status || '',
        employees: values.employees || '',
      }

      await updateCompanyMutation.mutateAsync({
        companyId: props.company.id,
        data: updateData,
      })

      toast.showSuccess('Company updated successfully!')
    } else {
      // Create new company
      await createCompanyMutation.mutateAsync({
        name: values.name || '',
        email: values.email || '',
        phone: values.phone || null,
        industry: values.industry || '',
        status: values.status || '',
        employees: values.employees || '',
      })

      toast.showSuccess('Company created successfully!')
    }

    resetForm()
    emits('close')
  } catch (error: any) {
    // Store error with context
    const context = isEditMode.value ? 'updateCompany' : 'createCompany'
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
            | 'phone'
            | 'industry'
            | 'status'
            | 'employees',
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
          <Input
            type="text"
            v-bind="componentField"
            placeholder="Company name"
          />
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
            placeholder="company@example.com"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="phone">
      <FormItem>
        <FormLabel>Phone</FormLabel>
        <FormControl>
          <Input type="tel" v-bind="componentField" placeholder="+1-555-0000" />
        </FormControl>
        <FormMessage />
        <p class="text-xs text-muted-foreground mt-1">
          Optional: Company phone number
        </p>
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="industry">
      <FormItem>
        <FormLabel>Industry</FormLabel>
        <FormControl>
          <Select v-bind="componentField">
            <SelectTrigger>
              <SelectValue placeholder="Select an industry" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="industry in industries"
                :key="industry.value"
                :value="industry.value"
              >
                {{ industry.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="status">
      <FormItem>
        <FormLabel>Status</FormLabel>
        <FormControl>
          <Select v-bind="componentField">
            <SelectTrigger>
              <SelectValue placeholder="Select a status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="status in statuses"
                :key="status.value"
                :value="status.value"
              >
                <div class="flex items-center gap-2">
                  <component :is="status.icon" class="size-4 shrink-0" />
                  {{ status.label }}
                </div>
              </SelectItem>
            </SelectContent>
          </Select>
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="employees">
      <FormItem>
        <FormLabel>Employees</FormLabel>
        <FormControl>
          <RadioGroup v-bind="componentField" class="flex flex-col space-y-1">
            <div
              v-for="size in employeeSizes"
              :key="size.value"
              class="flex items-center space-x-2"
            >
              <RadioGroupItem :id="size.value" :value="size.value" />
              <label
                :for="size.value"
                class="text-sm font-normal leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
              >
                {{ size.label }}
              </label>
            </div>
          </RadioGroup>
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <Button
      type="submit"
      class="w-full"
      :disabled="
        isEditMode
          ? updateCompanyMutation.isPending.value
          : createCompanyMutation.isPending.value
      "
    >
      <UiSpinner
        v-if="
          isEditMode
            ? updateCompanyMutation.isPending.value
            : createCompanyMutation.isPending.value
        "
        class="mr-2"
      />
      {{ isEditMode ? 'Update Company' : 'Create Company' }}
    </Button>
  </form>
</template>
