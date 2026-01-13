<script setup lang="ts">
import type { Customer } from '@/pages/customers/services/customers.service'

import {
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/components/ui/form'
import CustomerSwitcher from '@/pages/invoices/components/customer-switcher.vue'

interface IProps {
  customers?: Customer[]
  isFieldDirty?: boolean | ((path: any) => boolean)
}

withDefaults(defineProps<IProps>(), {
  isFieldDirty: false,
  customers: () => [],
})
</script>

<template>
  <div class="space-y-4">
    <h3 class="text-sm font-semibold text-muted-foreground uppercase">
      Invoice Details
    </h3>
    <FormField
      v-slot="{ componentField }"
      name="customer_id"
      :validate-on-blur="!isFieldDirty"
    >
      <FormItem>
        <FormLabel>Bill To</FormLabel>
        <FormControl>
          <CustomerSwitcher
            :customers="customers"
            :selected-customer-id="componentField.modelValue"
            @select="componentField['onUpdate:modelValue']"
          />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>
  </div>
</template>
