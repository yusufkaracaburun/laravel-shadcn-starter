<script setup lang="ts">
import { Filter } from 'lucide-vue-next'

import type { IInvoiceFilters } from '@/services/invoices.service'

import { Button } from '@/components/ui/button'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { useGetCustomersQuery } from '@/services/customers.service'

interface IInvoicesFilterProps {
  filters: IInvoiceFilters
  onFiltersChange: (filters: IInvoiceFilters) => void
  onClear: () => void
}

const props = defineProps<IInvoicesFilterProps>()

// Fetch customers for dropdown (with larger page size)
const { data: customersResponse } = useGetCustomersQuery(1, 100, [], {}, [])
const customers = computed(() => customersResponse.value?.data?.data ?? [])

const localFilters = ref<IInvoiceFilters>({ ...props.filters })

watch(
  () => props.filters,
  (newFilters) => {
    localFilters.value = { ...newFilters }
  },
  { deep: true },
)

function updateFilter(key: keyof IInvoiceFilters, value: any) {
  localFilters.value = {
    ...localFilters.value,
    [key]: value || undefined,
  }
  props.onFiltersChange(localFilters.value)
}

function clearFilters() {
  localFilters.value = {}
  props.onClear()
}

const hasActiveFilters = computed(() => {
  return Object.keys(localFilters.value).length > 0
})
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button variant="outline" class="h-8">
        <Filter class="mr-2 size-4" />
        Filters
        <span
          v-if="hasActiveFilters"
          class="ml-2 rounded-full bg-primary px-2 py-0.5 text-xs text-primary-foreground"
        >
          {{ Object.keys(localFilters).length }}
        </span>
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-[250px] p-0" align="start">
      <div class="p-4 space-y-4">
        <div class="space-y-2">
          <label class="text-sm font-medium">Status</label>
          <Select
            :model-value="localFilters.status"
            @update:model-value="
              (value) => updateFilter('status', value === 'all' ? undefined : value)
            "
          >
            <SelectTrigger>
              <SelectValue placeholder="All statuses" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all"> All statuses </SelectItem>
              <SelectItem value="draft"> Draft </SelectItem>
              <SelectItem value="sent"> Sent </SelectItem>
              <SelectItem value="paid"> Paid </SelectItem>
              <SelectItem value="overdue"> Overdue </SelectItem>
              <SelectItem value="cancelled"> Cancelled </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="space-y-2">
          <label class="text-sm font-medium">Customer</label>
          <Select
            :model-value="localFilters.customer_id ? localFilters.customer_id.toString() : 'all'"
            @update:model-value="
              (value) => updateFilter('customer_id', value === 'all' ? undefined : Number(value))
            "
          >
            <SelectTrigger>
              <SelectValue placeholder="All customers" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all"> All customers </SelectItem>
              <SelectItem
                v-for="customer in customers"
                :key="customer.id"
                :value="customer.id.toString()"
              >
                {{ customer.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="space-y-2">
          <label class="text-sm font-medium">Invoice Number</label>
          <input
            v-model="localFilters.invoice_number"
            type="text"
            placeholder="Filter by invoice number"
            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            @input="updateFilter('invoice_number', ($event.target as HTMLInputElement).value)"
          />
        </div>

        <div class="space-y-2">
          <label class="text-sm font-medium">Date From</label>
          <input
            v-model="localFilters.date"
            type="date"
            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            @input="updateFilter('date', ($event.target as HTMLInputElement).value)"
          />
        </div>

        <Button v-if="hasActiveFilters" variant="ghost" class="w-full" @click="clearFilters">
          Clear Filters
        </Button>
      </div>
    </PopoverContent>
  </Popover>
</template>
