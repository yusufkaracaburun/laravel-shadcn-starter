<script lang="ts" setup>
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { computed, watch } from 'vue'

import { FormField } from '@/components/ui/form'
import { useGetCustomersQuery } from '@/services/customers.service'
import { useInvoices } from '@/composables/use-invoices'
import { useRouter } from 'vue-router'

import type { Invoice } from '../data/schema'

const props = defineProps<{
  invoice: Invoice | null
}>()
const emits = defineEmits(['close'])

const router = useRouter()

const { createInvoice, updateInvoice } = useInvoices()

// Fetch customers for dropdown (with larger page size)
const { data: customersResponse } = useGetCustomersQuery(1, 100, [], {}, [])
const customers = computed(() => customersResponse.value?.data?.data ?? [])

const formSchema = toTypedSchema(
  z.object({
    customer_id: z.number().min(1, 'Customer is required'),
    invoice_number: z.string().nullable().optional(),
    date: z.string().min(1, 'Date is required'),
    due_days: z.number().min(1, 'Due days must be at least 1'),
    date_due: z.string().min(1, 'Due date is required'),
    status: z.enum(['draft', 'sent', 'paid', 'overdue', 'cancelled']),
    notes: z.string().nullable().optional(),
  }),
)

// Format date for input field (YYYY-MM-DD)
function formatDateForInput(dateString: string | null | undefined): string {
  if (!dateString) return ''
  // Try parsing as "d-m-Y H:i:s" format first
  if (dateString.includes('-') && dateString.includes(' ')) {
    const [datePart] = dateString.split(' ')
    const [day, month, year] = datePart.split('-')
    if (day && month && year && year.length === 4) {
      return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
    }
  }
  // Try parsing as "Y-m-d" format
  const date = new Date(dateString)
  if (!isNaN(date.getTime())) {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
  }
  return dateString
}

// Compute initial values reactively
const getInitialValues = () => ({
  customer_id: props.invoice?.customer_id ?? 0,
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
          customer_id: newInvoice.customer_id ?? 0,
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
          customer_id: 0,
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

    emits('close')
  } catch (error) {
    // Error handling is done in the composable
    // Just log for debugging
    console.error('Invoice form submission error:', error)
  }
})
</script>

<template>
  <form class="space-y-4" @submit="onSubmit">
    <FormField v-slot="{ componentField }" name="customer_id" :validate-on-blur="!isFieldDirty">
      <UiFormItem>
        <UiFormLabel>Customer</UiFormLabel>
        <UiFormControl>
          <UiSelect
            :model-value="componentField.modelValue?.toString()"
            @update:model-value="(value) => componentField.onUpdate(Number(value))"
          >
            <UiSelectTrigger>
              <UiSelectValue placeholder="Select a customer" />
            </UiSelectTrigger>
            <UiSelectContent>
              <UiSelectItem
                v-for="customer in customers"
                :key="customer.id"
                :value="customer.id.toString()"
              >
                {{ customer.name }}
              </UiSelectItem>
            </UiSelectContent>
          </UiSelect>
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

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      <FormField v-slot="{ componentField }" name="date" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Date</UiFormLabel>
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

    <FormField v-slot="{ componentField }" name="status" :validate-on-blur="!isFieldDirty">
      <UiFormItem>
        <UiFormLabel>Status</UiFormLabel>
        <UiFormControl>
          <UiSelect v-bind="componentField">
            <UiSelectTrigger>
              <UiSelectValue placeholder="Select status" />
            </UiSelectTrigger>
            <UiSelectContent>
              <UiSelectItem value="draft">Draft</UiSelectItem>
              <UiSelectItem value="sent">Sent</UiSelectItem>
              <UiSelectItem value="paid">Paid</UiSelectItem>
              <UiSelectItem value="overdue">Overdue</UiSelectItem>
              <UiSelectItem value="cancelled">Cancelled</UiSelectItem>
            </UiSelectContent>
          </UiSelect>
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="notes" :validate-on-blur="!isFieldDirty">
      <UiFormItem>
        <UiFormLabel>Notes</UiFormLabel>
        <UiFormControl>
          <UiTextarea placeholder="Additional notes..." v-bind="componentField" />
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>

    <UiButton type="submit" class="w-full" :disabled="isSubmitting">
      {{ isSubmitting ? 'Submitting...' : invoice ? 'Update Invoice' : 'Create Invoice' }}
    </UiButton>
  </form>
</template>
