<script setup lang="ts">
import type { IDataTableFilterProps } from '@/components/data-table/types'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
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
import { FilterIcon } from '@/composables/use-icons.composable'
import { useGetCustomersQuery } from '@/services/customers.service'

import type { IInvoiceFilters } from '../models/invoice'

import { INVOICE_STATUSES } from '../data/data'

const props = defineProps<IDataTableFilterProps<IInvoiceFilters>>()

// Fetch customers for dropdown
const { data: customersResponse } = useGetCustomersQuery(1, 100, [], {}, [])
const customers = computed(() => {
  const response = customersResponse.value?.data
  if (!response) {
    return []
  }
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
    [key]: value,
  }
  props.onFiltersChange(localFilters.value)
}

function clearFilters() {
  localFilters.value = {}
  props.onClearFilters()
}

function handleStatusChange(value: string) {
  updateFilter('status', value === 'all' ? null : value)
}

function handleCustomerChange(value: string) {
  updateFilter('customer_id', value === 'all' ? null : Number(value))
}

function handleInputChange(key: keyof IInvoiceFilters, value: string) {
  updateFilter(key, value)
}

const selectedStatus = computed(() => localFilters.value.status || 'all')

const selectedCustomer = computed(() =>
  localFilters.value.customer_id
    ? localFilters.value.customer_id.toString()
    : 'all',
)

const activeFilterCount = computed(
  () =>
    Object.keys(localFilters.value).filter(
      key => localFilters.value[key as keyof IInvoiceFilters],
    ).length,
)
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button variant="outline" class="h-8">
        <FilterIcon class="mr-2 size-4" />
        {{ $t('invoices.filters.title') }}
        <span
          v-if="activeFilterCount > 0"
          class="ml-2 rounded-full bg-primary px-2 py-0.5 text-xs text-primary-foreground"
        >
          {{ activeFilterCount }}
        </span>
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-[250px] p-0" align="start">
      <div class="p-4 space-y-4">
        <!-- Status Filter -->
        <div class="space-y-2">
          <Label for="status-filter" class="text-sm font-medium">
            {{ $t('invoices.filters.status') }}
          </Label>
          <Select
            id="status-filter"
            :model-value="selectedStatus"
            @update:model-value="(value) => handleStatusChange(String(value))"
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

        <!-- Customer Filter -->
        <div class="space-y-2">
          <Label for="customer-filter" class="text-sm font-medium">
            {{ $t('invoices.filters.customer') }}
          </Label>
          <Select
            id="customer-filter"
            :model-value="selectedCustomer"
            @update:model-value="(value) => handleCustomerChange(String(value))"
          >
            <SelectTrigger>
              <SelectValue :placeholder="$t('invoices.filters.allCustomers')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">
                {{ $t('invoices.filters.allCustomers') }}
              </SelectItem>
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

        <!-- Invoice Number Filter -->
        <div class="space-y-2">
          <Label for="invoice-number-filter" class="text-sm font-medium">
            {{ $t('invoices.filters.invoiceNumber') }}
          </Label>
          <Input
            id="invoice-number-filter"
            :model-value="localFilters.invoice_number || ''"
            :placeholder="$t('invoices.filters.invoiceNumberPlaceholder')"
            @update:model-value="
              (value) => handleInputChange('invoice_number', String(value))
            "
          />
        </div>

        <!-- Date Filter -->
        <div class="space-y-2">
          <Label for="date-filter" class="text-sm font-medium">
            {{ $t('invoices.filters.dateFrom') }}
          </Label>
          <Input
            id="date-filter"
            type="date"
            :model-value="localFilters.date || ''"
            @update:model-value="
              (value) => handleInputChange('date', String(value))
            "
          />
        </div>

        <!-- Clear Filters Button -->
        <Button
          v-if="activeFilterCount > 0"
          variant="ghost"
          class="w-full"
          @click="clearFilters"
        >
          {{ $t('invoices.filters.clearFilters') }}
        </Button>
      </div>
    </PopoverContent>
  </Popover>
</template>
