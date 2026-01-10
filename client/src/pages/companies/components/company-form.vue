<script lang="ts" setup>
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'

import type {
  ICompany,
  ICreateCompanyRequest,
  IUpdateCompanyRequest,
} from '@/pages/companies/models/companies'

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
import { useCompanies } from '@/pages/companies/composables/use-companies.composable'
import { setFormFieldErrors } from '@/utils/form'

import { employeeSizes, industries, statuses } from '../data/data'
import { createCompanyFormSchema, editCompanyFormSchema } from '../data/schema'

const props = defineProps<{
  company?: ICompany | null
}>()

const emits = defineEmits<{
  close: []
}>()

const {
  createCompany,
  updateCompany,
  isCreating,
  isUpdating,
  getCompanyFormInitialValues,
} = useCompanies()

const isEditMode = computed(() => !!props.company)
const formSchema = computed(() =>
  isEditMode.value ? editCompanyFormSchema : createCompanyFormSchema,
)

const initialValues = computed(() => getCompanyFormInitialValues(props.company))

const form = useForm({
  validationSchema: computed(() => toTypedSchema(formSchema.value)),
  initialValues: initialValues.value,
})

const { handleSubmit, setFieldError, resetForm } = form

watch(
  () => props.company,
  () => {
    resetForm({ values: initialValues.value })
  },
  { immediate: true },
)

const validFields = [
  'name',
  'email',
  'phone',
  'industry',
  'status',
  'employees',
] as const

function prepareRequestData(
  values: any,
  isEdit: boolean,
): IUpdateCompanyRequest | ICreateCompanyRequest {
  if (isEdit && props.company) {
    return {
      name: values.name,
      email: values.email,
      phone: values.phone || null,
      industry: values.industry,
      status: values.status,
      employees: values.employees,
    }
  }

  return {
    name: values.name,
    email: values.email,
    phone: values.phone || null,
    industry: values.industry,
    status: values.status,
    employees: values.employees,
  }
}

const onSubmit = handleSubmit(async (values) => {
  try {
    const requestData = prepareRequestData(values, isEditMode.value)

    if (isEditMode.value && props.company) {
      await updateCompany(
        props.company.id,
        requestData as IUpdateCompanyRequest,
      )
    } else {
      await createCompany(requestData as ICreateCompanyRequest)
    }

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
      :disabled="isEditMode ? isUpdating : isCreating"
    >
      <UiSpinner v-if="isEditMode ? isUpdating : isCreating" class="mr-2" />
      {{ isEditMode ? 'Update Company' : 'Create Company' }}
    </Button>
  </form>
</template>
