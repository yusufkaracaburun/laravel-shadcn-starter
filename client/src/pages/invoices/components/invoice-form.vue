<script lang="ts" setup>
import { computed, watch, ref } from 'vue'
import { useRouter } from 'vue-router'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { Plus, Pencil, Trash2 } from 'lucide-vue-next'

import { FormField } from '@/components/ui/form'
import { Button } from '@/components/ui/button'
import { useInvoices } from '@/composables/use-invoices'
import { useGetCustomersQuery } from '@/services/customers.service'

import type { Invoice, InvoiceItem } from '../data/schema'

import type { Item } from '@/services/items.service'
import { formatDateForInput, formatMoney, formatNumber } from '../utils/formatters'
import CustomerSwitcher from './customer-switcher.vue'
import InvoiceItemSelector from './invoice-item-selector.vue'
import InvoiceItemForm from './invoice-item-form.vue'
import { calculateItemTotals, calculateInvoiceTotals } from '../utils/calculations'

const props = defineProps<{
  invoice: Invoice | null
  nextInvoiceNumber?: string | null
  items?: Item[]
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

// Helper function to get today's date in YYYY-MM-DD format
function getTodayDate() {
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

// Helper function to calculate due date from invoice date and due days
function calculateDueDate(invoiceDate: string, dueDays: number): string {
  if (!invoiceDate || !dueDays) {
    return ''
  }
  try {
    const dateObj = new Date(invoiceDate)
    if (!Number.isNaN(dateObj.getTime())) {
      const dueDate = new Date(dateObj)
      dueDate.setDate(dueDate.getDate() + dueDays)
      const year = dueDate.getFullYear()
      const month = String(dueDate.getMonth() + 1).padStart(2, '0')
      const day = String(dueDate.getDate()).padStart(2, '0')
      return `${year}-${month}-${day}`
    }
  }
  catch {
    // Ignore date parsing errors
  }
  return ''
}

// Compute initial values reactively
const getInitialValues = () => {
  const invoiceDate = formatDateForInput(props.invoice?.date) || getTodayDate()
  const dueDays = props.invoice?.due_days ?? 30
  let calculatedDueDate = ''
  if (props.invoice?.date_due) {
    calculatedDueDate = formatDateForInput(props.invoice.date_due)
  }
  else {
    calculatedDueDate = calculateDueDate(invoiceDate, dueDays)
  }

  return {
    customer_id: props.invoice?.customer_id ?? undefined,
    invoice_number: props.invoice?.invoice_number ?? props.nextInvoiceNumber ?? null,
    date: invoiceDate,
    due_days: dueDays,
    date_due: calculatedDueDate,
    status: props.invoice?.status ?? 'draft',
    notes: props.invoice?.notes ?? null,
  }
}

const { values, isFieldDirty, handleSubmit, isSubmitting, resetForm, setFieldValue } = useForm({
  validationSchema: formSchema,
  initialValues: getInitialValues(),
})

// Local items state - used for both new and existing invoices
type LocalInvoiceItem = {
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
const localItems = ref<LocalInvoiceItem[]>([])
const editingItemIndex = ref<number | null>(null)
const showAddForm = ref(false)

// Initialize local items from invoice items when editing existing invoice
watch(() => props.invoice, (newInvoice) => {
  if (newInvoice?.items && newInvoice.items.length > 0) {
    // Convert invoice items to local format
    localItems.value = newInvoice.items.map((item) => {
      // Extract unit_price value
      let unitPrice = 0
      if (typeof item.unit_price === 'object' && 'amount' in item.unit_price) {
        unitPrice = Number.parseFloat(item.unit_price.amount) / 100
      }
      else if (typeof item.unit_price === 'number') {
        unitPrice = item.unit_price
      }

      // Extract totals from API response or recalculate if missing/invalid
      let totalExclVat = 0
      let totalVat = 0
      let totalInclVat = 0

      if (typeof item.total_excl_vat === 'object' && 'amount' in item.total_excl_vat) {
        totalExclVat = Number.parseFloat(item.total_excl_vat.amount) / 100
      }
      else if (typeof item.total_excl_vat === 'number') {
        totalExclVat = item.total_excl_vat
      }

      if (typeof item.total_vat === 'object' && 'amount' in item.total_vat) {
        totalVat = Number.parseFloat(item.total_vat.amount) / 100
      }
      else if (typeof item.total_vat === 'number') {
        totalVat = item.total_vat
      }

      if (typeof item.total_incl_vat === 'object' && 'amount' in item.total_incl_vat) {
        totalInclVat = Number.parseFloat(item.total_incl_vat.amount) / 100
      }
      else if (typeof item.total_incl_vat === 'number') {
        totalInclVat = item.total_incl_vat
      }

      // Recalculate if totals are missing or zero (shouldn't happen, but safety check)
      if (totalExclVat === 0 && unitPrice > 0 && item.quantity > 0) {
        const calculated = calculateItemTotals(item.quantity, unitPrice, item.vat_rate)
        totalExclVat = calculated.totalExclVat
        totalVat = calculated.totalVat
        totalInclVat = calculated.totalInclVat
      }

      return {
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
  }
  else {
    // Reset if no items or new invoice
    localItems.value = []
  }
  editingItemIndex.value = null
  showAddForm.value = false
}, { immediate: true, deep: true })

function handleItemSelected(itemData: any) {
  // Add to local state
  // Ensure vat_rate is a number
  const vatRate = typeof itemData.vat_rate === 'string' ? Number.parseInt(itemData.vat_rate) : Number(itemData.vat_rate)
  const totals = calculateItemTotals(itemData.quantity, itemData.unit_price, vatRate)
  localItems.value.push({
    ...itemData,
    vat_rate: vatRate,
    total_excl_vat: totals.totalExclVat,
    total_vat: totals.totalVat,
    total_incl_vat: totals.totalInclVat,
    sort_order: localItems.value.length,
  })
  showAddForm.value = false
}

function handleItemsSelected(itemsData: any[]) {
  // Add multiple items to local state
  itemsData.forEach((itemData) => {
    // Ensure vat_rate is a number
    const vatRate = typeof itemData.vat_rate === 'string' ? Number.parseInt(itemData.vat_rate) : Number(itemData.vat_rate)
    const totals = calculateItemTotals(itemData.quantity, itemData.unit_price, vatRate)
    localItems.value.push({
      ...itemData,
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
  const vatRate = typeof itemData.vat_rate === 'string' ? Number.parseInt(itemData.vat_rate) : Number(itemData.vat_rate)
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
  }
  else {
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

function startEditItem(item: InvoiceItem | any, index: number) {
  editingItemIndex.value = index
  showAddForm.value = false
}

function startAddItem() {
  editingItemIndex.value = null
  showAddForm.value = true
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
  () => [props.invoice, props.nextInvoiceNumber],
  (values) => {
    const [newInvoice, nextInvoiceNumber] = values as [Invoice | null, string | null | undefined]
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
    }
    else {
      // Reset to default values when invoice is null (create mode)
      const todayDate = getTodayDate()
      const defaultDueDays = 30
      const calculatedDueDate = calculateDueDate(todayDate, defaultDueDays)
      resetForm({
        values: {
          customer_id: undefined,
          invoice_number: (nextInvoiceNumber ?? null) as string | null,
          date: todayDate,
          due_days: defaultDueDays,
          date_due: calculatedDueDate,
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

    // Prepare items data from local state
    const itemsData = localItems.value.map(item => ({
      description: item.description || null,
      quantity: item.quantity,
      unit_price: item.unit_price,
      vat_rate: item.vat_rate,
      unit: item.unit ?? null,
      sort_order: item.sort_order ?? 0,
    }))

    const backendData: any = {
      customer_id: formValues.customer_id,
      invoice_number: formValues.invoice_number || null,
      date: formValues.date,
      due_days: formValues.due_days,
      date_due: formValues.date_due,
      status: formValues.status,
      notes: formValues.notes || null,
    }

    // Always include items
    backendData.items = itemsData || []

    if (props.invoice?.id) {
      // Update existing invoice
      await updateInvoice(props.invoice.id, backendData)
      // Navigate to invoice detail page
      router.push({ name: '/invoices/[id]', params: { id: props.invoice.id.toString() } })
    }
    else {
      // Create new invoice
      const response = await createInvoice(backendData)
      // Navigate to the new invoice detail page
      const newInvoice = response?.data
      if (newInvoice?.id) {
        router.push({ name: '/invoices/[id]', params: { id: newInvoice.id.toString() } })
      }
      else {
        // Fallback: navigate to invoices list
        router.push('/invoices')
      }
    }

    emits('submit', formValues)
    emits('close')
  }
  catch (error) {
    // Error handling is done in the composable
    // Just log for debugging
    console.error('Invoice form submission error:', error)
  }
})

// Calculate invoice totals once (computed for efficiency and consistency)
const invoiceTotals = computed(() => calculateInvoiceTotals(localItems.value))

// Convert localItems to InvoiceItem format for preview
const itemsForPreview = computed(() => {
  return localItems.value.map(item => ({
    id: item.id || 0,
    invoice_id: props.invoice?.id || 0,
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
  })) as InvoiceItem[]
})

// Expose form values and methods for parent component
defineExpose({
  values,
  handleSubmit: onSubmit,
  isSubmitting,
  resetForm,
  setFieldValue,
  items: itemsForPreview, // Expose items for preview
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

      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
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
    </div>

    <!-- Invoice Items Section -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-muted-foreground uppercase">Invoice Items</h3>
        <div class="flex gap-2">
          <InvoiceItemSelector :invoice-id="invoice?.id || 0" :items="items ?? []"
            @itemsSelected="handleItemsSelected" />
          <Button v-if="!showAddForm && editingItemIndex === null" size="sm" variant="outline" @click="startAddItem">
            <Plus class="mr-2 size-4" />
            Add Item
          </Button>
        </div>
      </div>

      <!-- Items management using local state -->
      <div class="space-y-4">
        <!-- Add/Edit Form -->
        <div v-if="showAddForm || editingItemIndex !== null" class="rounded-lg border p-4">
          <InvoiceItemForm
            :item="editingItemIndex !== null && editingItemIndex >= 0 ? (localItems[editingItemIndex] as any) || null : null"
            @save="(data) => handleItemSave(data, editingItemIndex ?? undefined)"
            @cancel="() => { editingItemIndex = null; showAddForm = false }" />
        </div>

        <!-- Items Table -->
        <div v-if="localItems.length === 0 && !showAddForm"
          class="flex flex-col items-center justify-center py-8 text-center">
          <p class="text-sm text-muted-foreground mb-4">No items added yet</p>
        </div>

        <div v-else-if="localItems.length > 0" class="space-y-2">
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b">
                  <th class="text-left p-2 font-medium text-muted-foreground">Name</th>
                  <th class="text-right p-2 font-medium text-muted-foreground">Quantity</th>
                  <th class="text-right p-2 font-medium text-muted-foreground">Unit</th>
                  <th class="text-right p-2 font-medium text-muted-foreground">Unit Price</th>
                  <th class="text-right p-2 font-medium text-muted-foreground">VAT Rate</th>
                  <th class="text-right p-2 font-medium text-muted-foreground">Incl. VAT</th>
                  <th class="text-right p-2 font-medium text-muted-foreground">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in localItems" :key="index" class="border-b hover:bg-muted/50">
                  <td class="p-2 font-medium">{{ item.description || '—' }}</td>
                  <td class="p-2 text-right">{{ formatNumber(item.quantity, 2) }}</td>
                  <td class="p-2 text-right">{{ item.unit || '—' }}</td>
                  <td class="p-2 text-right">{{ formatMoney(item.unit_price) }}</td>
                  <td class="p-2 text-right">{{ item.vat_rate }}%</td>
                  <td class="p-2 text-right font-semibold">{{ formatMoney(item.total_incl_vat) }}</td>
                  <td class="p-2 text-right">
                    <div class="flex justify-end gap-1">
                      <Button v-if="editingItemIndex !== index" variant="ghost" size="sm"
                        @click="startEditItem(item, index)">
                        <Pencil class="size-4" />
                      </Button>
                      <Button v-if="editingItemIndex !== index" variant="ghost" size="sm"
                        @click="handleItemDelete(index)">
                        <Trash2 class="size-4" />
                      </Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Totals Summary -->
          <div class="mt-4 rounded-lg border bg-muted/50 p-4">
            <div class="grid grid-cols-2 gap-4 md:grid-cols-5 text-sm">
              <div>
                <p class="text-muted-foreground">Subtotal</p>
                <p class="font-semibold">{{ formatMoney(invoiceTotals.subtotal) }}</p>
              </div>
              <div>
                <p class="text-muted-foreground">VAT 0%</p>
                <p class="font-semibold">{{ formatMoney(invoiceTotals.totalVat0) }}</p>
              </div>
              <div>
                <p class="text-muted-foreground">VAT 9%</p>
                <p class="font-semibold">{{ formatMoney(invoiceTotals.totalVat9) }}</p>
              </div>
              <div>
                <p class="text-muted-foreground">VAT 21%</p>
                <p class="font-semibold">{{ formatMoney(invoiceTotals.totalVat21) }}</p>
              </div>
              <div>
                <p class="text-muted-foreground">Total</p>
                <p class="font-bold text-lg">{{ formatMoney(invoiceTotals.total) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
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
