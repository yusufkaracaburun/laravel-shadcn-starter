<script setup lang="ts">
import { Pencil, Trash2 } from 'lucide-vue-next'

import { Button } from '@/components/ui/button'

import { formatMoney, formatNumber } from '../utils/formatters'

interface IInvoiceItem {
  id?: number
  description: string | null
  quantity: number
  unit: string | null
  unit_price: number
  vat_rate: number
  total_incl_vat: number
  [key: string]: unknown
}

interface IProps {
  items: IInvoiceItem[]
  editingItemIndex?: number | null
}

defineProps<IProps>()

const emits = defineEmits<{
  edit: [item: IInvoiceItem, index: number]
  delete: [index: number]
}>()

function handleEdit(item: IInvoiceItem, index: number) {
  emits('edit', item, index)
}

function handleDelete(index: number) {
  emits('delete', index)
}
</script>

<template>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b">
          <th class="text-left p-2 font-medium text-muted-foreground">
            Name
          </th>
          <th class="text-right p-2 font-medium text-muted-foreground">
            Quantity
          </th>
          <th class="text-right p-2 font-medium text-muted-foreground">
            Unit
          </th>
          <th class="text-right p-2 font-medium text-muted-foreground">
            Unit Price
          </th>
          <th class="text-right p-2 font-medium text-muted-foreground">
            VAT Rate
          </th>
          <th class="text-right p-2 font-medium text-muted-foreground">
            Incl. VAT
          </th>
          <th class="text-right p-2 font-medium text-muted-foreground">
            Actions
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in items" :key="index" class="border-b hover:bg-muted/50">
          <td class="p-2 font-medium">
            {{ item.name || '—' }}
          </td>
          <td class="p-2 text-right">
            {{ formatNumber(item.quantity, 2) }}
          </td>
          <td class="p-2 text-right">
            {{ item.unit || '—' }}
          </td>
          <td class="p-2 text-right">
            {{ formatMoney(item.unit_price) }}
          </td>
          <td class="p-2 text-right">
            {{ item.vat_rate }}%
          </td>
          <td class="p-2 text-right font-semibold">
            {{ formatMoney(item.total_incl_vat) }}
          </td>
          <td class="p-2 text-right">
            <div class="flex justify-end gap-1">
              <Button
                v-if="editingItemIndex !== index"
                variant="ghost"
                size="sm"
                @click="handleEdit(item, index)"
              >
                <Pencil class="size-4" />
              </Button>
              <Button
                v-if="editingItemIndex !== index"
                variant="ghost"
                size="sm"
                @click="handleDelete(index)"
              >
                <Trash2 class="size-4" />
              </Button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
