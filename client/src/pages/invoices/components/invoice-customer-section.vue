<script setup lang="ts">
import type { Customer } from '@/services/customers.service'

import {
  FormField,
  UiFormControl,
  UiFormItem,
  UiFormLabel,
  UiFormMessage,
} from '@/components/ui/form'

import CustomerSwitcher from './customer-switcher.vue'

interface IProps {
  customers?: Customer[]
  isFieldDirty?: boolean | ((path: any) => boolean)
}

withDefaults(defineProps<IProps>(), {
  isFieldDirty: false,
})
</script>

<template>
  <div class="space-y-4">
    <h3 class="text-sm font-semibold text-muted-foreground uppercase">
      Invoice Details
    </h3>
    <FormField v-slot="{ componentField }" name="customer_id" :validate-on-blur="!isFieldDirty">
      <UiFormItem>
        <UiFormLabel>Bill To</UiFormLabel>
        <UiFormControl>
          <CustomerSwitcher
            :customers="customers ?? []"
            :selected-customer-id="componentField.modelValue"
            @select="componentField['onUpdate:modelValue']"
          />
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>
  </div>
</template>
