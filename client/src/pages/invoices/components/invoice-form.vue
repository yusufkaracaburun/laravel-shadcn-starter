<script lang="ts" setup>
import { computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { z } from 'zod'

import { FormField } from '@/components/ui/form'
import { useInvoices } from '@/composables/use-invoices'
import { useGetCustomersQuery } from '@/services/customers.service'

import type { Invoice } from '../data/schema'
import { formatDateForInput } from '../utils/formatters'
import CustomerSwitcher from './customer-switcher.vue'

const props = defineProps<{
  invoice: Invoice | null
}>()
const emits = defineEmits(['close', 'submit'])

const router = useRouter()

const { createInvoice, updateInvoice } = useInvoices()

// Fetch customers for dropdown (with larger page size)
const { data: customersResponse } = useGetCustomersQuery(1, 100, [], {}, [])
const customers = computed(() => customersResponse.value?.data?.data ?? [])

const formSchema = toTypedSchema(
  z.object({
    customer_id: z.preprocess(
      (val) => (val === undefined || val === null || val === '' ? undefined : Number(val)),
      z.number().min(1, 'Customer is required'),
    ),
    invoice_number: z.string().nullable().optional(),
    date: z.string().min(1, 'Date is required'),
    due_days: z.number().min(1, 'Due days must be at least 1'),
    date_due: z.string().min(1, 'Due date is required'),
    status: z.enum(['draft', 'sent', 'paid', 'overdue', 'cancelled']),
    notes: z.string().nullable().optional(),
  }),
)

// Compute initial values reactively
const getInitialValues = () => ({
  customer_id: props.invoice?.customer_id ?? undefined,
  invoice_number: props.invoice?.invoice_number ?? null,
  date: formatDateForInput(props.invoice?.date),
  due_days: props.invoice?.due_days ?? 30,
  date_due: formatDateForInput(props.invoice?.date_due),
  status: props.invoice?.status ?? 'draft',
  notes: props.invoice?.notes ?? null,
})

const { values, isFieldDirty, handleSubmit, isSubmitting, resetForm, setFieldValue } = useForm({
  validationSchema: formSchema,
  initialValues: getInitialValues(),
})

// Auto-calculate date_due when date or due_days changes
watch(
  [() => values.date, () => values.due_days],
  ([date, dueDays]) => {
    if (date && dueDays) {
      try {
        const dateObj = new Date(date)
        if (!isNaN(dateObj.getTime())) {
          const dueDate = new Date(dateObj)
          dueDate.setDate(dueDate.getDate() + dueDays)
          const year = dueDate.getFullYear()
          const month = String(dueDate.getMonth() + 1).padStart(2, '0')
          const day = String(dueDate.getDate()).padStart(2, '0')
          setFieldValue('date_due', `${year}-${month}-${day}`)
        }
      } catch (error) {
        // Ignore date parsing errors
      }
    }
  },
  { immediate: true },
)

// Reset form when invoice changes
watch(
  () => props.invoice,
  (newInvoice) => {
    if (newInvoice) {
      resetForm({
        values: {
          customer_id: newInvoice.customer_id ?? undefined,
          invoice_number: newInvoice.invoice_number ?? null,
          date: formatDateForInput(newInvoice.date),
          due_days: newInvoice.due_days ?? 30,
          date_due: formatDateForInput(newInvoice.date_due),
          status: newInvoice.status ?? 'draft',
          notes: newInvoice.notes ?? null,
        },
      })
    } else {
      // Reset to default values when invoice is null (create mode)
      resetForm({
        values: {
          customer_id: undefined,
          invoice_number: null,
          date: '',
          due_days: 30,
          date_due: '',
          status: 'draft',
          notes: null,
        },
      })
    }
  },
  { deep: true, immediate: true },
)

const onSubmit = handleSubmit(async (formValues) => {
  try {
    if (!formValues.customer_id) {
      // Customer is required - validation should catch this, but double-check
      return
    }
    const backendData = {
      customer_id: formValues.customer_id,
      invoice_number: formValues.invoice_number || null,
      date: formValues.date,
      due_days: formValues.due_days,
      date_due: formValues.date_due,
      status: formValues.status,
      notes: formValues.notes || null,
    }

    if (props.invoice?.id) {
      // Update existing invoice
      await updateInvoice(props.invoice.id, backendData)
      // Navigate to invoice detail page
      router.push({ name: '/invoices/[id]', params: { id: props.invoice.id.toString() } })
    } else {
      // Create new invoice
      const response = await createInvoice(backendData)
      // Navigate to the new invoice detail page
      const newInvoice = response?.data
      if (newInvoice?.id) {
        router.push({ name: '/invoices/[id]', params: { id: newInvoice.id.toString() } })
      } else {
        // Fallback: navigate to invoices list
        router.push('/invoices')
      }
    }

    emits('submit', formValues)
    emits('close')
  } catch (error) {
    // Error handling is done in the composable
    // Just log for debugging
    console.error('Invoice form submission error:', error)
  }
})

// Expose form values and methods for parent component
defineExpose({
  values,
  handleSubmit: onSubmit,
  isSubmitting,
  resetForm,
  setFieldValue,
})
</script>

<template>
  <form class="space-y-6" @submit.prevent="onSubmit">
    <!-- Invoice Details Section -->
    <div class="space-y-4">
      <h3 class="text-sm font-semibold text-muted-foreground uppercase">Invoice Details</h3>

      <FormField v-slot="{ componentField }" name="customer_id" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Bill To</UiFormLabel>
          <UiFormControl>
            <CustomerSwitcher :customers="customers" :selected-customer-id="componentField.modelValue"
              @select="(customerId) => setFieldValue('customer_id', customerId)" />
          </UiFormControl>
          <UiFormMessage />
        </UiFormItem>
      </FormField>

      <FormField v-slot="{ componentField }" name="invoice_number" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Invoice Number</UiFormLabel>
          <UiFormControl>
            <UiInput type="text" placeholder="INV-001" v-bind="componentField" />
          </UiFormControl>
          <UiFormMessage />
        </UiFormItem>
      </FormField>
    </div>

    <!-- Dates Section -->
    <div class="space-y-4">
      <h3 class="text-sm font-semibold text-muted-foreground uppercase">Dates</h3>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <FormField v-slot="{ componentField }" name="date" :validate-on-blur="!isFieldDirty">
          <UiFormItem>
            <UiFormLabel>Invoice Date</UiFormLabel>
            <UiFormControl>
              <UiInput type="date" v-bind="componentField" />
            </UiFormControl>
            <UiFormMessage />
          </UiFormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="due_days" :validate-on-blur="!isFieldDirty">
          <UiFormItem>
            <UiFormLabel>Due Days</UiFormLabel>
            <UiFormControl>
              <UiInput type="number" min="1" v-bind="componentField" />
            </UiFormControl>
            <UiFormMessage />
          </UiFormItem>
        </FormField>
      </div>

      <FormField v-slot="{ componentField }" name="date_due" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Due Date</UiFormLabel>
          <UiFormControl>
            <UiInput type="date" v-bind="componentField" />
          </UiFormControl>
          <UiFormMessage />
        </UiFormItem>
      </FormField>
    </div>

    <!-- Notes Section -->
    <div class="space-y-4">
      <h3 class="text-sm font-semibold text-muted-foreground uppercase">Notes</h3>

      <FormField v-slot="{ componentField }" name="notes" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Notes</UiFormLabel>
          <UiFormControl>
            <UiTextarea placeholder="Additional notes..." v-bind="componentField" />
          </UiFormControl>
          <UiFormMessage />
        </UiFormItem>
      </FormField>
    </div>
  </form>
</template>
