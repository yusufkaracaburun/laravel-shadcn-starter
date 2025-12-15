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
      Dates
    </h3>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
      <FormField v-slot="{ componentField }" name="date" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Invoice Date</UiFormLabel>
          <UiFormControl>
            <UiInput type="date" v-bind="componentField" />
          </UiFormControl>
          <UiFormMessage />
        </UiFormItem>
      </FormField>

      <FormField v-slot="{ componentField }" name="due_days" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Due Days</UiFormLabel>
          <UiFormControl>
            <UiInput type="number" min="1" v-bind="componentField" />
          </UiFormControl>
          <UiFormMessage />
        </UiFormItem>
      </FormField>

      <FormField v-slot="{ componentField }" name="date_due" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Due Date</UiFormLabel>
          <UiFormControl>
            <UiInput type="date" v-bind="componentField" />
          </UiFormControl>
          <UiFormMessage />
        </UiFormItem>
      </FormField>
    </div>
  </div>
</template>
