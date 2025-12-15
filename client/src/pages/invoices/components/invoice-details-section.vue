<script setup lang="ts">
import { FormField } from '@/components/ui/form'
import {
  UiFormItem,
  UiFormLabel,
  UiFormControl,
  UiInput,
  UiFormMessage,
} from '@/components/ui/form'

interface Props {
  isFieldDirty?: boolean | ((path: any) => boolean)
}

withDefaults(defineProps<Props>(), {
  isFieldDirty: false,
})
</script>

<template>
  <div class="space-y-4">
    <h3 class="text-sm font-semibold text-muted-foreground uppercase">Invoice Details</h3>
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
</template>
