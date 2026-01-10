<script lang="ts" setup>
import { toTypedSchema } from '@vee-validate/zod'
import { Building2Icon, UserIcon } from '@/composables/use-icons.composable'
import { useForm } from 'vee-validate'
import { computed, watch } from 'vue'

import { FormField } from '@/components/ui/form'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import { useCustomers } from '@/composables/use-customers.composable'
import { setFormFieldErrors } from '@/utils/form'

import type { ICustomer } from '../models/customers'

import {
  createCustomerFormSchema,
  editCustomerFormSchema,
} from '../data/schema'

const props = defineProps<{
  customer?: ICustomer | null
}>()

const emits = defineEmits<{
  close: []
}>()

const {
  createCustomer,
  updateCustomer,
  isCreating,
  isUpdating,
  getCustomerFormInitialValues,
} = useCustomers()

const isEditMode = computed(() => !!props.customer)
const formSchema = computed(() =>
  isEditMode.value ? editCustomerFormSchema : createCustomerFormSchema,
)

const initialValues = computed(() =>
  getCustomerFormInitialValues(props.customer),
)

const form = useForm({
  validationSchema: computed(() => toTypedSchema(formSchema.value)),
  initialValues: initialValues.value,
})

const { values, handleSubmit, setFieldError, resetForm } = form

// Watch customer type to show/hide business fields
const customerType = computed(() => values.type)

// Reset form when customer changes
watch(
  () => props.customer,
  () => {
    resetForm({ values: initialValues.value })
  },
  { immediate: true },
)

const validFields = [
  'type',
  'name',
  'email',
  'phone',
  'address',
  'zipcode',
  'city',
  'country',
  'kvk_number',
  'vat_number',
  'iban_number',
] as const

function prepareRequestData(values: any): any {
  return {
    type: values.type,
    name: values.name,
    email: values.email || null,
    phone: values.phone || null,
    address: values.address || null,
    zipcode: values.zipcode || null,
    city: values.city || null,
    country: values.country || null,
    kvk_number: values.kvk_number || null,
    vat_number: values.vat_number || null,
    iban_number: values.iban_number || null,
  }
}

const onSubmit = handleSubmit(async (formValues) => {
  try {
    const requestData = prepareRequestData(formValues)

    if (isEditMode.value && props.customer) {
      await updateCustomer(props.customer.id, requestData)
    } else {
      await createCustomer(requestData)
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
    <FormField v-slot="{ componentField }" name="type">
      <UiFormItem class="space-y-1">
        <UiFormLabel>Customer Type</UiFormLabel>
        <UiFormMessage />
        <RadioGroup class="grid grid-cols-2 gap-4 pt-2" v-bind="componentField">
          <UiFormItem class="h-full w-full">
            <UiFormLabel
              class="[&:has([data-state=checked])>div]:border-primary flex flex-col cursor-pointer h-full w-full"
            >
              <UiFormControl>
                <RadioGroupItem value="private" class="sr-only" />
              </UiFormControl>
              <div
                class="p-2 border-2 rounded-md border-muted hover:border-accent transition-colors h-full w-full flex items-center justify-center"
              >
                <div class="flex flex-col items-center gap-2">
                  <UserIcon class="size-4 text-muted-foreground" />
                  <div class="font-semibold text-foreground">Private</div>
                </div>
              </div>
            </UiFormLabel>
          </UiFormItem>
          <UiFormItem class="h-full w-full">
            <UiFormLabel
              class="[&:has([data-state=checked])>div]:border-primary flex flex-col cursor-pointer h-full w-full"
            >
              <UiFormControl>
                <RadioGroupItem value="business" class="sr-only" />
              </UiFormControl>
              <div
                class="p-2 border-2 rounded-md border-muted hover:border-accent transition-colors h-full w-full flex items-center justify-center"
              >
                <div class="flex flex-col items-center gap-2">
                  <Building2Icon class="size-4 text-muted-foreground" />
                  <div class="font-semibold text-foreground">Business</div>
                </div>
              </div>
            </UiFormLabel>
          </UiFormItem>
        </RadioGroup>
      </UiFormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="name">
      <UiFormItem>
        <UiFormLabel>Name</UiFormLabel>
        <UiFormControl>
          <UiInput
            type="text"
            placeholder="Customer name"
            v-bind="componentField"
          />
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="email">
      <UiFormItem>
        <UiFormLabel>Email</UiFormLabel>
        <UiFormControl>
          <UiInput
            type="email"
            placeholder="customer@example.com"
            v-bind="componentField"
          />
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="phone">
      <UiFormItem>
        <UiFormLabel>Phone</UiFormLabel>
        <UiFormControl>
          <UiInput
            type="tel"
            placeholder="+31 6 12345678"
            v-bind="componentField"
          />
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      <FormField v-slot="{ componentField }" name="address">
        <UiFormItem>
          <UiFormLabel>Address</UiFormLabel>
          <UiFormControl>
            <UiInput
              type="text"
              placeholder="Street address"
              v-bind="componentField"
            />
          </UiFormControl>
          <UiFormMessage />
        </UiFormItem>
      </FormField>

      <FormField v-slot="{ componentField }" name="zipcode">
        <UiFormItem>
          <UiFormLabel>Zipcode</UiFormLabel>
          <UiFormControl>
            <UiInput
              type="text"
              placeholder="1234 AB"
              v-bind="componentField"
            />
          </UiFormControl>
          <UiFormMessage />
        </UiFormItem>
      </FormField>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      <FormField v-slot="{ componentField }" name="city">
        <UiFormItem>
          <UiFormLabel>City</UiFormLabel>
          <UiFormControl>
            <UiInput type="text" placeholder="City" v-bind="componentField" />
          </UiFormControl>
          <UiFormMessage />
        </UiFormItem>
      </FormField>

      <FormField v-slot="{ componentField }" name="country">
        <UiFormItem>
          <UiFormLabel>Country</UiFormLabel>
          <UiFormControl>
            <UiInput
              type="text"
              placeholder="NL"
              maxlength="2"
              v-bind="componentField"
            />
          </UiFormControl>
          <UiFormMessage />
        </UiFormItem>
      </FormField>
    </div>

    <template v-if="customerType === 'business'">
      <div class="border-t pt-4">
        <h3 class="mb-4 text-lg font-semibold">Business Information</h3>

        <FormField v-slot="{ componentField }" name="kvk_number">
          <UiFormItem>
            <UiFormLabel>KVK Number</UiFormLabel>
            <UiFormControl>
              <UiInput
                type="text"
                placeholder="12345678"
                v-bind="componentField"
              />
            </UiFormControl>
            <UiFormMessage />
          </UiFormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="vat_number">
          <UiFormItem>
            <UiFormLabel>VAT Number</UiFormLabel>
            <UiFormControl>
              <UiInput
                type="text"
                placeholder="NL123456789B01"
                v-bind="componentField"
              />
            </UiFormControl>
            <UiFormMessage />
          </UiFormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="iban_number">
          <UiFormItem>
            <UiFormLabel>IBAN Number</UiFormLabel>
            <UiFormControl>
              <UiInput
                type="text"
                placeholder="NL91ABNA0417164300"
                maxlength="34"
                v-bind="componentField"
              />
            </UiFormControl>
            <UiFormMessage />
          </UiFormItem>
        </FormField>
      </div>
    </template>

    <UiButton
      type="submit"
      class="w-full"
      :disabled="isEditMode ? isUpdating : isCreating"
    >
      {{ isEditMode ? 'Update Customer' : 'Create Customer' }}
    </UiButton>
  </form>
</template>
