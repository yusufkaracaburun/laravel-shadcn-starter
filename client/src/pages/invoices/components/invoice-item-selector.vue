<script lang="ts" setup>
import { computed, ref } from 'vue'

import type { IInvoiceItem } from '@/pages/invoices/models/invoice'

import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { CheckIcon, PlusIcon, SearchIcon } from '@/composables/use-icons'
import { formatMoney } from '@/pages/invoices/utils/formatters'

interface IProps {
  items?: IInvoiceItem[]
}

const props = withDefaults(defineProps<IProps>(), {
  items: () => [],
})

const emits = defineEmits<{
  itemsSelected: [items: IInvoiceItem[]]
}>()

const isOpen = ref(false)
const searchQuery = ref('')
const selectedItems = ref<Set<number>>(new Set())

const items = computed(() => props.items)

const filteredItems = computed(() => {
  if (!searchQuery.value.trim()) {
    return items.value.slice(0, 20)
  }

  const query = searchQuery.value.toLowerCase()
  return items.value
    .filter((item) => {
      const name = item.name?.toLowerCase() || ''
      const description = item.description?.toLowerCase() || ''
      return name.includes(query) || description.includes(query)
    })
    .slice(0, 20)
})

const selectedCount = computed(() => selectedItems.value.size)

function toggleItemSelection(itemId: number) {
  if (selectedItems.value.has(itemId)) {
    selectedItems.value.delete(itemId)
  } else {
    selectedItems.value.add(itemId)
  }
}

function isItemSelected(itemId: number): boolean {
  return selectedItems.value.has(itemId)
}

function handleAddSelected() {
  const selectedItemsData = filteredItems.value
    .filter(item => selectedItems.value.has(item.id))
    .map((item) => {
      const unitPrice = Number.parseFloat(item.unit_price.amount) / 100

      return {
        name: item.name,
        description: item.description || null,
        quantity: 1,
        unit: item.unit ?? null,
        unit_price: unitPrice,
        vat_rate: item.vat_rate || 21,
      }
    })

  if (selectedItemsData.length > 0) {
    emits('itemsSelected', selectedItemsData as IInvoiceItem[])
    selectedItems.value.clear()
    isOpen.value = false
    searchQuery.value = ''
  }
}

function handleClose() {
  selectedItems.value.clear()
  searchQuery.value = ''
  isOpen.value = false
}
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button variant="outline" size="sm">
        <PlusIcon class="mr-2 size-4" />
        Add from Catalog
      </Button>
    </DialogTrigger>
    <DialogContent class="max-w-2xl">
      <DialogHeader>
        <DialogTitle>Select Item from Catalog</DialogTitle>
        <DialogDescription>
          Choose an item from your catalog to add to this invoice
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-4">
        <div class="relative">
          <SearchIcon
            class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
          />
          <Input
            v-model="searchQuery"
            placeholder="Search items..."
            class="pl-9"
          />
        </div>

        <div
          v-if="selectedCount > 0"
          class="flex items-center justify-between rounded-lg border bg-muted/50 p-3"
        >
          <div class="text-sm font-medium">
            {{ selectedCount }} item{{ selectedCount === 1 ? '' : 's' }}
            selected
          </div>
          <Button size="sm" variant="ghost" @click="selectedItems.clear()">
            <X class="size-4" />
          </Button>
        </div>

        <div
          v-if="filteredItems.length === 0"
          class="flex items-center justify-center py-8"
        >
          <div class="text-sm text-muted-foreground">
            {{ searchQuery ? 'No items found' : 'No items available' }}
          </div>
        </div>

        <div v-else class="max-h-[400px] space-y-2 overflow-y-auto">
          <div
            v-for="item in filteredItems"
            :key="item.id"
            class="flex items-center gap-3 rounded-lg border p-4 hover:bg-muted/50 cursor-pointer transition-colors"
            :class="
              isItemSelected(item.id) ? 'border-primary bg-primary/5' : ''
            "
            @click="toggleItemSelection(item.id)"
          >
            <div
              class="flex size-5 shrink-0 items-center justify-center rounded-sm border-2 transition-colors"
              :class="
                isItemSelected(item.id)
                  ? 'border-primary bg-primary text-primary-foreground'
                  : 'border-muted'
              "
            >
              <CheckIcon v-if="isItemSelected(item.id)" class="size-3" />
            </div>
            <div class="flex-1">
              <div class="font-medium">
                {{ item.name }}
              </div>
              <div class="mt-1 flex gap-4 text-sm text-muted-foreground">
                <span>Price: {{ formatMoney(item.unit_price) }}</span>
                <span>VAT: {{ item.vat_rate }}%</span>
                <span v-if="item.unit">Unit: {{ item.unit }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <DialogFooter>
        <Button variant="outline" @click="handleClose">
          Cancel
        </Button>
        <Button :disabled="selectedCount === 0" @click="handleAddSelected">
          <PlusIcon class="mr-2 size-4" />
          Add
          {{
            selectedCount > 0
              ? `${selectedCount} item${selectedCount === 1 ? '' : 's'}`
              : 'Items'
          }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
