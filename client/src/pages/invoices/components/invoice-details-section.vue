<script setup lang="ts">
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form'
import { Input } from '@/components/ui/input'

interface IProps {
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
    <FormField v-slot="{ componentField }" name="invoice_number" :validate-on-blur="!isFieldDirty">
      <FormItem>
        <FormLabel>Invoice Number</FormLabel>
        <FormControl>
          <Input type="text" placeholder="INV-001" v-bind="componentField" />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>
  </div>
</template>
