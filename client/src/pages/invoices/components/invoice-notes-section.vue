<script setup lang="ts">
import {
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/components/ui/form'
import { Textarea } from '@/components/ui/textarea'

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
      Notes
    </h3>

    <FormField
      v-slot="{ componentField }"
      name="notes"
      :validate-on-blur="!isFieldDirty"
    >
      <FormItem>
        <FormLabel>Notes</FormLabel>
        <FormControl>
          <Textarea placeholder="Additional notes..." v-bind="componentField" />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>
  </div>
</template>
