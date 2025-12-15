<script setup lang="ts">
import { Plus } from 'lucide-vue-next'

import type { Item } from '@/services/items.service'

import { Button } from '@/components/ui/button'

import InvoiceItemForm from './invoice-item-form.vue'
import InvoiceItemSelector from './invoice-item-selector.vue'
import InvoiceItemsTable from './invoice-items-table.vue'
import InvoiceTotalsSummary from './invoice-totals-summary.vue'

interface IInvoiceItem {
  id?: number
  description: string | null
  quantity: number
  unit: string | null
  unit_price: number
  vat_rate: number
  total_excl_vat: number
  total_vat: number
  total_incl_vat: number
  sort_order: number
  [key: string]: unknown
}

interface IInvoiceTotals {
  subtotal: number
  totalVat0: number
  totalVat9: number
  totalVat21: number
  total: number
}

interface IProps {
  items: IInvoiceItem[]
  editingItemIndex: number | null
  showAddForm: boolean
  invoiceTotals: IInvoiceTotals
  invoiceId?: number
  catalogItems?: Item[]
}

const props = defineProps<IProps>()

const emits = defineEmits<{
  save: [itemData: any, itemIdOrIndex?: number]
  cancel: []
  edit: [item: IInvoiceItem, index: number]
  delete: [index: number]
  itemsSelected: [items: any[]]
  addItem: []
}>()

function handleSave(itemData: any) {
  emits('save', itemData, props.editingItemIndex ?? undefined)
}

function handleCancel() {
  emits('cancel')
}

function handleEdit(item: IInvoiceItem, index: number) {
  emits('edit', item, index)
}

function handleDelete(index: number) {
  emits('delete', index)
}

function handleItemsSelected(items: any[]) {
  emits('itemsSelected', items)
}

function handleAddItem() {
  emits('addItem')
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="text-sm font-semibold text-muted-foreground uppercase">
        Invoice Items
      </h3>
      <div class="flex gap-2">
        <InvoiceItemSelector
          :invoice-id="invoiceId || 0"
          :items="catalogItems ?? []"
          @items-selected="handleItemsSelected"
        />
        <Button
          v-if="!showAddForm && editingItemIndex === null"
          size="sm"
          variant="outline"
          @click="handleAddItem"
        >
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
          :item="
            editingItemIndex !== null && editingItemIndex >= 0
              ? (items[editingItemIndex] as any) || null
              : null
          "
          @save="handleSave"
          @cancel="handleCancel"
        />
      </div>

      <!-- Items Table -->
      <div
        v-if="items.length === 0 && !showAddForm"
        class="flex flex-col items-center justify-center py-8 text-center"
      >
        <p class="text-sm text-muted-foreground mb-4">
          No items added yet
        </p>
      </div>

      <div v-else-if="items.length > 0" class="space-y-2">
        <InvoiceItemsTable
          :items="items"
          :editing-item-index="editingItemIndex"
          @edit="handleEdit"
          @delete="handleDelete"
        />

        <InvoiceTotalsSummary
          :subtotal="invoiceTotals.subtotal"
          :total-vat0="invoiceTotals.totalVat0"
          :total-vat9="invoiceTotals.totalVat9"
          :total-vat21="invoiceTotals.totalVat21"
          :total="invoiceTotals.total"
        />
      </div>
    </div>
  </div>
</template>
