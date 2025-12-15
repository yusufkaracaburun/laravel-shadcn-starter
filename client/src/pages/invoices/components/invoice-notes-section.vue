<script setup lang="ts">
import { FormField } from '@/components/ui/form'

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

    <FormField v-slot="{ componentField }" name="notes" :validate-on-blur="!isFieldDirty">
      <UiFormItem>
        <UiFormLabel>Notes</UiFormLabel>
        <UiFormControl>
          <UiTextarea placeholder="Additional notes..." v-bind="componentField" />
        </UiFormControl>
        <UiFormMessage />
      </UiFormItem>
    </FormField>
  </div>
</template>
