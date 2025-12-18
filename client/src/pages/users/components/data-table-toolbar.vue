<script setup lang="ts">
import type { Table } from '@tanstack/vue-table'

import { X } from 'lucide-vue-next'
import { computed } from 'vue'

import type { FacetedFilterOption } from '@/components/data-table/types'
import type { User } from '@/services/users.service'

import DataTableFacetedFilter from '@/components/data-table/faceted-filter.vue'
import DataTableViewOptions from '@/components/data-table/view-options.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { useGetRolesQuery } from '@/services/users.service'

interface DataTableToolbarProps {
  table: Table<User>
}

const props = defineProps<DataTableToolbarProps>()

const isFiltered = computed(() => props.table.getState().columnFilters.length > 0)

// Fetch roles for the filter
const { data: rolesResponse } = useGetRolesQuery()

// Convert roles to FacetedFilterOption format
const roleOptions = computed<FacetedFilterOption[]>(() => {
  if (!rolesResponse.value?.data) {
    return []
  }
  return rolesResponse.value.data.map((role) => ({
    label: role.name,
    value: role.name,
  }))
})
</script>

<template>
  <div class="flex items-center justify-between">
    <div
      class="flex flex-col items-start flex-1 space-y-2 md:items-center md:space-x-2 md:space-y-0 md:flex-row"
    >
      <Input
        placeholder="Filter users by name..."
        :model-value="(table.getColumn('name')?.getFilterValue() as string) ?? ''"
        class="h-8 w-[150px] lg:w-[250px]"
        @input="table.getColumn('name')?.setFilterValue($event.target.value)"
      />

      <div class="space-x-2">
        <DataTableFacetedFilter
          v-if="table.getColumn('roles') && roleOptions.length > 0"
          :column="table.getColumn('roles')"
          title="Roles"
          :options="roleOptions"
        />
      </div>

      <Button
        v-if="isFiltered"
        variant="ghost"
        class="h-8 px-2 lg:px-3"
        @click="table.resetColumnFilters()"
      >
        Reset
        <X class="size-4 ml-2" />
      </Button>
    </div>
    <DataTableViewOptions :table="table" />
  </div>
</template>
