<script setup lang="ts">
import type { IDataTableFilterProps } from '@/components/data-table/types'

import { Button } from '@/components/ui/button'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { FilterIcon } from '@/composables/use-icons'
import { useGetCustomersQuery } from '@/services/customers.service'

import type { IInvoiceFilters } from '../models/invoice'

import { INVOICE_STATUSES } from '../data/data'

const props = defineProps<IDataTableFilterProps<IInvoiceFilters>>()

// Fetch customers for dropdown (with larger page size)
const { data: customersResponse } = useGetCustomersQuery(1, 100, [], {}, [])
const customers = computed(() => {
  const response = customersResponse.value?.data
  if (!response) return []
  // Type guard: check if response is array or single object
  if (Array.isArray(response)) {
    return response.flatMap(r =>
      r && typeof r === 'object' && 'data' in r ? r.data : [],
    )
  }
  return response && typeof response === 'object' && 'data' in response
    ? response.data
    : []
})

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
  props.onClearFilters()
}

function handleStatusChange(value: any) {
  const statusValue =
    value === null || value === undefined ? null : String(value)
  updateFilter(
    'status',
    statusValue === 'all' || statusValue === null ? undefined : statusValue,
  )
}

const selectedStatus = computed(() => localFilters.value.status || 'all')

const hasActiveFilters = computed(() => {
  return Object.keys(localFilters.value).length > 0
})
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button variant="outline" class="h-8">
        <FilterIcon class="mr-2 size-4" />
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
          <label for="status-filter" class="text-sm font-medium">
            {{ $t('invoices.filters.status') }}
          </label>
          <Select
            id="status-filter"
            :model-value="selectedStatus"
            @update:model-value="handleStatusChange"
          >
            <SelectTrigger>
              <SelectValue :placeholder="$t('invoices.filters.allStatuses')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">
                {{ $t('invoices.filters.allStatuses') }}
              </SelectItem>
              <SelectItem
                v-for="status in INVOICE_STATUSES"
                :key="status.value"
                :value="status.value"
              >
                {{ status.label }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="space-y-2">
          <label for="customer-filter" class="text-sm font-medium"
            >Customer</label
          >
          <Select
            id="customer-filter"
            :model-value="
              localFilters.customer_id
                ? localFilters.customer_id.toString()
                : 'all'
            "
            @update:model-value="
              (value) =>
                updateFilter(
                  'customer_id',
                  value === 'all' ? undefined : Number(value),
                )
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
          <label for="invoice-number-filter" class="text-sm font-medium"
            >Invoice Number</label
          >
          <input
            id="invoice-number-filter"
            v-model="localFilters.invoice_number"
            type="text"
            placeholder="Filter by invoice number"
            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            @input="
              updateFilter(
                'invoice_number',
                ($event.target as HTMLInputElement).value,
              )
            "
          />
        </div>

        <div class="space-y-2">
          <label for="date-filter" class="text-sm font-medium">Date From</label>
          <input
            id="date-filter"
            v-model="localFilters.date"
            type="date"
            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            @input="
              updateFilter('date', ($event.target as HTMLInputElement).value)
            "
          />
        </div>

        <Button
          v-if="hasActiveFilters"
          variant="ghost"
          class="w-full"
          @click="clearFilters"
        >
          Clear Filters
        </Button>
      </div>
    </PopoverContent>
  </Popover>
</template>
