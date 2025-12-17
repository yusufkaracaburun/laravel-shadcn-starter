<script lang="ts" setup>
import { computed, nextTick, ref, watch } from 'vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { useRouter } from 'vue-router'

import type { Customer } from '@/services/customers.service'
import type { Item } from '@/services/items.service'

import { useInvoices } from '@/composables/use-invoices'
import { mapObjectDeep } from '@/utils/form'
import { calculateDueDate, getTodayDate } from '@/utils/date'

import { invoiceFormSchema, type TInvoice, type TInvoiceItem } from '../data/schema'

import {
  calculateInvoiceTotals,
  calculateItemTotals,
  formatCalculatedTotals,
  toMoneyObject,
} from '../utils/calculations'
import { formatDateForInput } from '../utils/formatters'
import InvoiceCustomerSection from './invoice-customer-section.vue'
import InvoiceDatesSection from './invoice-dates-section.vue'
import InvoiceDetailsSection from './invoice-details-section.vue'
import InvoiceItemsManagement from './invoice-items-management.vue'
import InvoiceNotesSection from './invoice-notes-section.vue'

const props = defineProps<{
  modelValue: TInvoice | null
  nextInvoiceNumber?: string | null
  items?: Item[]
  customers?: Customer[]
}>()
const emits = defineEmits(['close', 'submit', 'update:modelValue', 'update:formItems'])

const router = useRouter()

const { createInvoice, updateInvoice } = useInvoices()

// Use customers from props (from prerequisites) or fallback to empty array
const customers = computed(() => props.customers ?? [])

const formSchema = toTypedSchema(invoiceFormSchema)

// Internal flag to prevent recursive updates from v-model
let blockEmits = false

// Compute initial values reactively
function getInitialValues() {
  const invoiceDate = formatDateForInput(props.modelValue?.date) || getTodayDate()
  const dueDays = props.modelValue?.due_days ?? 30
  let calculatedDueDate = ''
  if (props.modelValue?.date_due) {
    calculatedDueDate = formatDateForInput(props.modelValue.date_due)
  } else {
    calculatedDueDate = calculateDueDate(invoiceDate, dueDays)
  }

  return {
    customer_id: props.modelValue?.customer_id ?? undefined,
    invoice_number: props.modelValue?.invoice_number ?? props.nextInvoiceNumber ?? null,
    date: invoiceDate,
    due_days: dueDays,
    date_due: calculatedDueDate,
    status: props.modelValue?.status ?? 'draft',
    notes: props.modelValue?.notes ?? null,
  }
}

const {
  values,
  isFieldDirty,
  handleSubmit,
  isSubmitting,
  resetForm,
  setFieldValue,
  setFieldError,
  errors,
} = useForm({
  validationSchema: formSchema,
  initialValues: getInitialValues(),
})

// Local items state - used for both new and existing invoices
interface ILocalInvoiceItem {
  name: string
  description: string | null
  quantity: number
  unit_price: number
  vat_rate: number
  unit: string | null
  total_excl_vat: number
  total_vat: number
  total_incl_vat: number
  sort_order: number
  id?: number
}
const localItems = ref<ILocalInvoiceItem[]>(
  (Array.isArray(props.modelValue?.items)
    ? props.modelValue?.items
    : (props.modelValue?.items as any)?.data || []
  ).map((item: any) => {
    let unitPrice = 0
    if (typeof item.unit_price === 'object' && 'amount' in item.unit_price) {
      unitPrice = Number.parseFloat(item.unit_price.amount) / 100
    } else if (typeof item.unit_price === 'number') {
      unitPrice = item.unit_price
    }

    let totalExclVat = 0
    let totalVat = 0
    let totalInclVat = 0

    if (typeof item.total_excl_vat === 'object' && 'amount' in item.total_excl_vat) {
      totalExclVat = Number.parseFloat(item.total_excl_vat.amount) / 100
    } else if (typeof item.total_excl_vat === 'number') {
      totalExclVat = item.total_excl_vat
    }

    if (typeof item.total_vat === 'object' && 'amount' in item.total_vat) {
      totalVat = Number.parseFloat(item.total_vat.amount) / 100
    } else if (typeof item.total_vat === 'number') {
      totalVat = item.total_vat
    }

    if (typeof item.total_incl_vat === 'object' && 'amount' in item.total_incl_vat) {
      totalInclVat = Number.parseFloat(item.total_incl_vat.amount) / 100
    } else if (typeof item.total_incl_vat === 'number') {
      totalInclVat = item.total_incl_vat
    }

    if (totalExclVat === 0 && unitPrice > 0 && item.quantity > 0) {
      const calculated = calculateItemTotals(item.quantity, unitPrice, item.vat_rate)
      totalExclVat = calculated.totalExclVat
      totalVat = calculated.totalVat
      totalInclVat = calculated.totalInclVat
    }

    return {
      name: item.name,
      description: item.description,
      quantity: item.quantity,
      unit_price: unitPrice,
      vat_rate: item.vat_rate,
      unit: (item as any).unit ?? null,
      total_excl_vat: totalExclVat,
      total_vat: totalVat,
      total_incl_vat: totalInclVat,
      sort_order: item.sort_order,
    }
  }),
)
const editingItemIndex = ref<number | null>(null)
const showAddForm = ref(false)

// Raw numeric totals from calculations
const rawInvoiceTotals = computed(() => calculateInvoiceTotals(localItems.value))

// Formatted invoice totals (Money objects)
const invoiceTotals = computed(() => formatCalculatedTotals(rawInvoiceTotals.value))

function handleItemsSelected(itemsData: any[]) {
  // Add multiple items to local state
  itemsData.forEach((itemData) => {
    // Ensure vat_rate is a number
    const vatRate =
      typeof itemData.vat_rate === 'string'
        ? Number.parseInt(itemData.vat_rate)
        : Number(itemData.vat_rate)
    const totals = calculateItemTotals(itemData.quantity, itemData.unit_price, vatRate)
    localItems.value.push({
      ...itemData,
      name: itemData.name || itemData.description, // Ensure name is always set
      vat_rate: vatRate,
      total_excl_vat: totals.totalExclVat,
      total_vat: totals.totalVat,
      total_incl_vat: totals.totalInclVat,
      sort_order: localItems.value.length,
    })
  })
  showAddForm.value = false
}

function handleItemSave(itemData: any, itemIdOrIndex?: number) {
  // Ensure vat_rate is a number
  const vatRate =
    typeof itemData.vat_rate === 'string'
      ? Number.parseInt(itemData.vat_rate)
      : Number(itemData.vat_rate)
  const totals = calculateItemTotals(itemData.quantity, itemData.unit_price, vatRate)
  if (typeof itemIdOrIndex === 'number' && itemIdOrIndex >= 0) {
    // Update existing item
    localItems.value[itemIdOrIndex] = {
      ...itemData,
      vat_rate: vatRate,
      unit: itemData.unit ?? null,
      total_excl_vat: totals.totalExclVat,
      total_vat: totals.totalVat,
      total_incl_vat: totals.totalInclVat,
      sort_order: localItems.value[itemIdOrIndex].sort_order ?? itemIdOrIndex,
    }
  } else {
    // Add new item
    localItems.value.push({
      ...itemData,
      vat_rate: vatRate,
      unit: itemData.unit ?? null,
      total_excl_vat: totals.totalExclVat,
      total_vat: totals.totalVat,
      total_incl_vat: totals.totalInclVat,
      sort_order: localItems.value.length,
    })
  }
  editingItemIndex.value = null
  showAddForm.value = false
}

function handleItemDelete(itemIdOrIndex: number) {
  // eslint-disable-next-line no-alert
  if (window.confirm('Are you sure you want to delete this item?')) {
    localItems.value.splice(itemIdOrIndex, 1)
    // Update sort_order
    localItems.value.forEach((item, index) => {
      item.sort_order = index
    })
  }
}

function startEditItem(_item: TInvoiceItem | any, index: number) {
  editingItemIndex.value = index
  showAddForm.value = false
}

function startAddItem() {
  editingItemIndex.value = null
  showAddForm.value = true
}

function cancelItemEdit() {
  editingItemIndex.value = null
  showAddForm.value = false
}

// Auto-calculate date_due when date or due_days changes
watch(
  [() => values.date, () => values.due_days],
  ([date, dueDays]) => {
    if (date && dueDays) {
      const calculatedDueDate = calculateDueDate(date, dueDays)
      if (calculatedDueDate) {
        setFieldValue('date_due', calculatedDueDate)
      }
    }
  },
  { immediate: true },
)

// Reset form when invoice or nextInvoiceNumber changes
watch(
  () => [props.modelValue, props.nextInvoiceNumber],
  (values) => {
    const [newInvoice, nextInvoiceNumber] = values as [TInvoice | null, string | null | undefined]
    if (newInvoice) {
      setFieldValue('customer_id', newInvoice.customer_id ?? undefined)
      setFieldValue('invoice_number', newInvoice.invoice_number ?? null)
      setFieldValue('date', formatDateForInput(newInvoice.date))
      setFieldValue('due_days', newInvoice.due_days ?? 30)
      setFieldValue('date_due', formatDateForInput(newInvoice.date_due))
      setFieldValue('status', newInvoice.status ?? 'draft')
      setFieldValue('notes', newInvoice.notes ?? null)
    } else {
      // Reset to default values when invoice is null (create mode)
      const todayDate = getTodayDate()
      const defaultDueDays = 30
      const calculatedDueDate = calculateDueDate(todayDate, defaultDueDays)
      setFieldValue('customer_id', undefined)
      setFieldValue('invoice_number', (nextInvoiceNumber ?? null) as string | null)
      setFieldValue('date', todayDate)
      setFieldValue('due_days', defaultDueDays)
      setFieldValue('date_due', calculatedDueDate)
      setFieldValue('status', 'draft')
      setFieldValue('notes', null)
    }
  },
  { deep: true, immediate: true },
)

// Convert localItems to InvoiceItem format for preview
const itemsForPreview = computed(() => {
  return localItems.value.map((item) => ({
    id: item.id || 0,
    invoice_id: props.modelValue?.id || 0,
    name: item.name,
    description: item.description,
    quantity: item.quantity,
    unit_price: item.unit_price,
    vat_rate: item.vat_rate,
    unit: item.unit ?? null,
    total_excl_vat: item.total_excl_vat,
    total_vat: item.total_vat,
    total_incl_vat: item.total_incl_vat,
    sort_order: item.sort_order,
    created_at: '',
    updated_at: '',
  })) as TInvoiceItem[]
})

// Watch for changes in values and localItems and emit to parent
watch(
  values,
  (newValues) => {
    if (blockEmits) return
    emits('update:modelValue', {
      ...(newValues as TInvoice),
      subtotal: toMoneyObject(rawInvoiceTotals.value.subtotal),
      total_vat_0: toMoneyObject(rawInvoiceTotals.value.totalVat0),
      total_vat_9: toMoneyObject(rawInvoiceTotals.value.totalVat9),
      total_vat_21: toMoneyObject(rawInvoiceTotals.value.totalVat21),
      total: toMoneyObject(rawInvoiceTotals.value.total),
      total_excl_vat: toMoneyObject(rawInvoiceTotals.value.subtotal),
      total_vat: toMoneyObject(
        rawInvoiceTotals.value.totalVat0 +
          rawInvoiceTotals.value.totalVat9 +
          rawInvoiceTotals.value.totalVat21,
      ),
      items: itemsForPreview.value,
    } as TInvoice)
  },
  { deep: true },
)

watch(
  localItems,
  (_newItems) => {
    if (blockEmits) return
    emits('update:formItems', itemsForPreview.value)
  },
  { deep: true },
)

// Watch props.modelValue for external changes and update form (but block emits during this)
watch(
  () => props.modelValue,
  (newInvoice) => {
    if (newInvoice && newInvoice.id !== values.id) {
      blockEmits = true
      setFieldValue('customer_id', newInvoice.customer_id ?? undefined)
      setFieldValue('invoice_number', newInvoice.invoice_number ?? null)
      setFieldValue('date', formatDateForInput(newInvoice.date))
      setFieldValue('due_days', newInvoice.due_days ?? 30)
      setFieldValue('date_due', formatDateForInput(newInvoice.date_due))
      setFieldValue('status', newInvoice.status ?? 'draft')
      setFieldValue('notes', newInvoice.notes ?? null)
      localItems.value = (
        Array.isArray(newInvoice.items) ? newInvoice.items : (newInvoice.items as any)?.data || []
      ).map((item: any) => {
        let unitPrice = 0
        if (typeof item.unit_price === 'object' && 'amount' in item.unit_price) {
          unitPrice = Number.parseFloat(item.unit_price.amount) / 100
        } else if (typeof item.unit_price === 'number') {
          unitPrice = item.unit_price
        }
        let totalExclVat = 0
        let totalVat = 0
        let totalInclVat = 0
        if (typeof item.total_excl_vat === 'object' && 'amount' in item.total_excl_vat) {
          totalExclVat = Number.parseFloat(item.total_excl_vat.amount) / 100
        } else if (typeof item.total_excl_vat === 'number') {
          totalExclVat = item.total_excl_vat
        }
        if (typeof item.total_vat === 'object' && 'amount' in item.total_vat) {
          totalVat = Number.parseFloat(item.total_vat.amount) / 100
        } else if (typeof item.total_vat === 'number') {
          totalVat = item.total_vat
        }
        if (typeof item.total_incl_vat === 'object' && 'amount' in item.total_incl_vat) {
          totalInclVat = Number.parseFloat(item.total_incl_vat.amount) / 100
        } else if (typeof item.total_incl_vat === 'number') {
          totalInclVat = item.total_incl_vat
        }
        if (totalExclVat === 0 && unitPrice > 0 && item.quantity > 0) {
          const calculated = calculateItemTotals(item.quantity, unitPrice, item.vat_rate)
          totalExclVat = calculated.totalExclVat
          totalVat = calculated.totalVat
          totalInclVat = calculated.totalInclVat
        }
        return {
          name: item.name,
          description: item.description,
          quantity: item.quantity,
          unit_price: unitPrice,
          vat_rate: item.vat_rate,
          unit: (item as any).unit ?? null,
          total_excl_vat: totalExclVat,
          total_vat: totalVat,
          total_incl_vat: totalInclVat,
          sort_order: item.sort_order,
        }
      })
      nextTick(() => {
        blockEmits = false
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

    // Prepare items data from local state
    const itemsData = localItems.value.map((item) => ({
      name: item.name,
      description: item.description || null,
      quantity: item.quantity,
      unit_price: item.unit_price,
      vat_rate: item.vat_rate,
      unit: item.unit ?? null,
      sort_order: item.sort_order ?? 0,
    }))

    let backendData: any = {
      customer_id: formValues.customer_id,
      invoice_number: formValues.invoice_number || null,
      date: formValues.date,
      due_days: formValues.due_days,
      date_due: formValues.date_due,
      status: formValues.status,
      notes: formValues.notes || null,
    }

    // Convert empty strings to null recursively
    backendData = mapObjectDeep(backendData, (value) => (value === '' ? null : value))

    // Add calculated totals to backendData, ensuring they are numbers or strings as expected by backend
    backendData.subtotal = rawInvoiceTotals.value.subtotal
    backendData.total_vat_0 = rawInvoiceTotals.value.totalVat0
    backendData.total_vat_9 = rawInvoiceTotals.value.totalVat9
    backendData.total_vat_21 = rawInvoiceTotals.value.totalVat21
    backendData.total = rawInvoiceTotals.value.total
    backendData.total_excl_vat = rawInvoiceTotals.value.subtotal
    backendData.total_vat =
      rawInvoiceTotals.value.totalVat0 +
      rawInvoiceTotals.value.totalVat9 +
      rawInvoiceTotals.value.totalVat21

    // Always include items
    backendData.items = itemsData || []

    if (props.modelValue?.id) {
      // Update existing invoice
      await updateInvoice(props.modelValue.id, backendData)
      // Navigate to invoice detail page
      router.push({ name: '/invoices/[id]', params: { id: props.modelValue.id.toString() } })
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
  } catch (error: any) {
    // Handle backend validation errors (422)
    if (error.response?.status === 422) {
      const backendErrors = error.response.data.errors || {}
      // Set field errors from backend response
      Object.keys(backendErrors).forEach((field) => {
        const fieldErrors = backendErrors[field]
        if (Array.isArray(fieldErrors) && fieldErrors.length > 0) {
          // Map backend field names to form field names if needed
          const formFieldName = field === 'invoice_number' ? 'invoice_number' : field
          setFieldError(formFieldName as any, fieldErrors[0])
        }
      })
    }
    // Error handling is also done in the composable (toast messages)
    // Log for debugging
    console.error('Invoice form submission error:', error)
  }
})

// Expose form values and methods for parent component
defineExpose({
  handleSubmit: onSubmit,
  isSubmitting,
  resetForm,
  setFieldValue,
  errors, // Expose errors for preview component
})
</script>

<template>
  <form class="space-y-6" @submit.prevent="onSubmit">
    <InvoiceCustomerSection :customers="customers" :is-field-dirty="isFieldDirty" />

    <InvoiceDetailsSection :is-field-dirty="isFieldDirty" />

    <InvoiceDatesSection :is-field-dirty="isFieldDirty" />

    <InvoiceItemsManagement
      :items="localItems"
      :editing-item-index="editingItemIndex"
      :show-add-form="showAddForm"
      :invoice-totals="invoiceTotals"
      :invoice-id="props.modelValue?.id"
      :catalog-items="items"
      @save="handleItemSave"
      @cancel="cancelItemEdit"
      @edit="startEditItem"
      @delete="handleItemDelete"
      @items-selected="handleItemsSelected"
      @add-item="startAddItem"
    />

    <InvoiceNotesSection :is-field-dirty="isFieldDirty" />
  </form>
</template>
