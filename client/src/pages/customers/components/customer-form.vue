<script lang="ts" setup>
import { toTypedSchema } from '@vee-validate/zod'
import { Building2, User } from 'lucide-vue-next'
import { useForm } from 'vee-validate'
import { z } from 'zod'

import { FormField } from '@/components/ui/form'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import { useCustomers } from '@/composables/use-customers'

import type { Customer } from '../data/schema'

const props = defineProps<{
  customer: Customer | null
}>()
const emits = defineEmits(['close'])

const { createCustomer, updateCustomer } = useCustomers()

const formSchema = toTypedSchema(
  z.object({
    type: z
      .enum(['business', 'private'])
      .default(props.customer?.type ?? 'private'),
    name: z
      .string()
      .min(2, 'Name must be at least 2 characters')
      .max(255, 'Name must not exceed 255 characters')
      .default(props.customer?.name ?? ''),
    email: z
      .string()
      .email('Invalid email address')
      .max(255, 'Email must not exceed 255 characters')
      .nullable()
      .default(props.customer?.email ?? null),
    phone: z
      .string()
      .nullable()
      .default(props.customer?.phone ?? null),
    address: z
      .string()
      .nullable()
      .default(props.customer?.address ?? null),
    zipcode: z
      .string()
      .nullable()
      .default(props.customer?.zipcode ?? null),
    city: z
      .string()
      .nullable()
      .default(props.customer?.city ?? null),
    country: z
      .string()
      .max(2, 'Country code must be 2 characters')
      .nullable()
      .default(props.customer?.country ?? null),
    kvk_number: z
      .string()
      .nullable()
      .default(props.customer?.kvk_number ?? null),
    vat_number: z
      .string()
      .nullable()
      .default(props.customer?.vat_number ?? null),
    iban_number: z
      .string()
      .max(34, 'IBAN must not exceed 34 characters')
      .nullable()
      .default(props.customer?.iban_number ?? null),
  }),
)

// Compute initial values reactively
const initialValues = computed(() => ({
  type: props.customer?.type ?? 'private',
  name: props.customer?.name ?? '',
  email: props.customer?.email ?? null,
  phone: props.customer?.phone ?? null,
  address: props.customer?.address ?? null,
  zipcode: props.customer?.zipcode ?? null,
  city: props.customer?.city ?? null,
  country: props.customer?.country ?? null,
  kvk_number: props.customer?.kvk_number ?? null,
  vat_number: props.customer?.vat_number ?? null,
  iban_number: props.customer?.iban_number ?? null,
}))

const { values, isFieldDirty, handleSubmit, isSubmitting, resetForm } = useForm(
  {
    validationSchema: formSchema,
    initialValues: initialValues.value,
  },
)

// Watch customer type to show/hide business fields
const customerType = computed(() => values.type)

// Reset form when customer changes
watch(
  () => props.customer,
  (newCustomer) => {
    if (newCustomer) {
      resetForm({
        values: {
          type: newCustomer.type ?? 'private',
          name: newCustomer.name ?? '',
          email: newCustomer.email ?? null,
          phone: newCustomer.phone ?? null,
          address: newCustomer.address ?? null,
          zipcode: newCustomer.zipcode ?? null,
          city: newCustomer.city ?? null,
          country: newCustomer.country ?? null,
          kvk_number: newCustomer.kvk_number ?? null,
          vat_number: newCustomer.vat_number ?? null,
          iban_number: newCustomer.iban_number ?? null,
        },
      })
    }
  },
  { deep: true },
)

const onSubmit = handleSubmit(async (formValues) => {
  try {
    const backendData = {
      type: formValues.type,
      name: formValues.name,
      email: formValues.email || null,
      phone: formValues.phone || null,
      address: formValues.address || null,
      zipcode: formValues.zipcode || null,
      city: formValues.city || null,
      country: formValues.country || null,
      kvk_number: formValues.kvk_number || null,
      vat_number: formValues.vat_number || null,
      iban_number: formValues.iban_number || null,
    }

    if (props.customer?.id) {
      // Update existing customer
      await updateCustomer(props.customer.id, backendData)
    }
    else {
      // Create new customer
      await createCustomer(backendData)
    }

    emits('close')
  }
  catch (error) {
    // Error handling is done in the composable
    // Just log for debugging
    console.error('Customer form submission error:', error)
  }
})
</script>

<template>
  <form class="space-y-4" @submit="onSubmit">
    <FormField
      v-slot="{ componentField }"
      type="radio"
      name="type"
      :validate-on-blur="!isFieldDirty"
    >
      <UiFormItem class="space-y-1">
        <UiFormLabel>Customer Type</UiFormLabel>
        <UiFormMessage />
        <RadioGroup class="grid grid-cols-2 gap-4 pt-2" v-bind="componentField">
          <UiFormItem class="h-full">
            <UiFormLabel
              class="[&:has([data-state=checked])>div]:border-primary flex flex-col cursor-pointer h-full"
            >
              <UiFormControl>
                <RadioGroupItem value="private" class="sr-only" />
              </UiFormControl>
              <div
                class="p-4 border-2 rounded-md border-muted hover:border-accent transition-colors h-full flex items-center justify-center"
              >
                <div class="flex flex-col items-center gap-3">
                  <User class="size-8 text-muted-foreground" />
                  <div class="text-center">
                    <div class="font-semibold text-foreground">
                      Private
                    </div>
                    <div class="text-xs font-normal text-muted-foreground mt-1">
                      Individual customer
                    </div>
                  </div>
                </div>
              </div>
            </UiFormLabel>
          </UiFormItem>
          <UiFormItem class="h-full">
            <UiFormLabel
              class="[&:has([data-state=checked])>div]:border-primary flex flex-col cursor-pointer h-full"
            >
              <UiFormControl>
                <RadioGroupItem value="business" class="sr-only" />
              </UiFormControl>
              <div
                class="p-4 border-2 rounded-md border-muted hover:border-accent transition-colors h-full flex items-center justify-center"
              >
                <div class="flex flex-col items-center gap-3">
                  <Building2 class="size-8 text-muted-foreground" />
                  <div class="text-center">
                    <div class="font-semibold text-foreground">
                      Business
                    </div>
                    <div class="text-xs font-normal text-muted-foreground mt-1">
                      Company or organization
                    </div>
                  </div>
                </div>
              </div>
            </UiFormLabel>
          </UiFormItem>
        </RadioGroup>
      </UiFormItem>
    </FormField>

    <FormField
      v-slot="{ componentField }"
      name="name"
      :validate-on-blur="!isFieldDirty"
    >
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

    <FormField
      v-slot="{ componentField }"
      name="email"
      :validate-on-blur="!isFieldDirty"
    >
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

    <FormField
      v-slot="{ componentField }"
      name="phone"
      :validate-on-blur="!isFieldDirty"
    >
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
      <FormField
        v-slot="{ componentField }"
        name="address"
        :validate-on-blur="!isFieldDirty"
      >
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

      <FormField
        v-slot="{ componentField }"
        name="zipcode"
        :validate-on-blur="!isFieldDirty"
      >
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
      <FormField
        v-slot="{ componentField }"
        name="city"
        :validate-on-blur="!isFieldDirty"
      >
        <UiFormItem>
          <UiFormLabel>City</UiFormLabel>
          <UiFormControl>
            <UiInput type="text" placeholder="City" v-bind="componentField" />
          </UiFormControl>
          <UiFormMessage />
        </UiFormItem>
      </FormField>

      <FormField
        v-slot="{ componentField }"
        name="country"
        :validate-on-blur="!isFieldDirty"
      >
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
        <h3 class="mb-4 text-lg font-semibold">
          Business Information
        </h3>

        <FormField
          v-slot="{ componentField }"
          name="kvk_number"
          :validate-on-blur="!isFieldDirty"
        >
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

        <FormField
          v-slot="{ componentField }"
          name="vat_number"
          :validate-on-blur="!isFieldDirty"
        >
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

        <FormField
          v-slot="{ componentField }"
          name="iban_number"
          :validate-on-blur="!isFieldDirty"
        >
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

    <UiButton type="submit" class="w-full" :disabled="isSubmitting">
      {{
        isSubmitting
          ? 'Submitting...'
          : customer
            ? 'Update Customer'
            : 'Create Customer'
      }}
    </UiButton>
  </form>
</template>
