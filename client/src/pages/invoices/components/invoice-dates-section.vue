<script setup lang="ts">
import {
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/components/ui/form'
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
    <h3 class="text-sm font-semibold text-muted-foreground uppercase">Dates</h3>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
      <FormField
        v-slot="{ componentField }"
        name="date"
        :validate-on-blur="!isFieldDirty"
      >
        <FormItem>
          <FormLabel>Invoice Date</FormLabel>
          <FormControl>
            <Input type="date" v-bind="componentField" />
          </FormControl>
          <FormMessage />
        </FormItem>
      </FormField>

      <FormField
        v-slot="{ componentField }"
        name="due_days"
        :validate-on-blur="!isFieldDirty"
      >
        <FormItem>
          <FormLabel>Due Days</FormLabel>
          <FormControl>
            <Input type="number" min="1" v-bind="componentField" />
          </FormControl>
          <FormMessage />
        </FormItem>
      </FormField>

      <FormField
        v-slot="{ componentField }"
        name="date_due"
        :validate-on-blur="!isFieldDirty"
      >
        <FormItem>
          <FormLabel>Due Date</FormLabel>
          <FormControl>
            <Input type="date" v-bind="componentField" />
          </FormControl>
          <FormMessage />
        </FormItem>
      </FormField>
    </div>
  </div>
</template>
