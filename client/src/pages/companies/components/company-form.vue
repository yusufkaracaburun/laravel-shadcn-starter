<script lang="ts" setup>
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { toast } from 'vue-sonner'
import { z } from 'zod'

import { FormField } from '@/components/ui/form'

import type { Company } from '../data/schema'

import { employeeSizes, industries, statuses } from '../data/data'

const props = defineProps<{
  company: Company | null
}>()
const emits = defineEmits(['close'])

const formSchema = toTypedSchema(
  z.object({
    name: z
      .string()
      .min(2)
      .max(100)
      .default(props.company?.name ?? ''),
    email: z
      .string()
      .email()
      .default(props.company?.email ?? ''),
    phone: z.string().default(props.company?.phone ?? ''),
    industry: z.string().default(props.company?.industry ?? ''),
    status: z.string().default(props.company?.status ?? ''),
    employees: z.string().default(props.company?.employees ?? ''),
  }),
)

const { isFieldDirty, handleSubmit } = useForm({
  validationSchema: formSchema,
})
const onSubmit = handleSubmit((values) => {
  toast('You submitted the following values:', {
    description: h(
      'pre',
      { class: 'mt-2 w-[340px] rounded-md bg-slate-950 p-4' },
      h('code', { class: 'text-white' }, JSON.stringify(values, null, 2)),
    ),
  })
  emits('close')
})
</script>

<template>
  <div>
    <form class="w-2/3 space-y-6" @submit="onSubmit">
      <FormField v-slot="{ componentField }" name="name" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Name</UiFormLabel>
          <UiFormControl>
            <UiInput type="text" placeholder="Company name" v-bind="componentField" />
          </UiFormControl>
          <UiFormDescription />
          <UiFormMessage />
        </UiFormItem>
      </FormField>
      <FormField v-slot="{ componentField }" name="email" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Email</UiFormLabel>
          <UiFormControl>
            <UiInput type="email" placeholder="company@example.com" v-bind="componentField" />
          </UiFormControl>
          <UiFormDescription />
          <UiFormMessage />
        </UiFormItem>
      </FormField>
      <FormField v-slot="{ componentField }" name="phone" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Phone</UiFormLabel>
          <UiFormControl>
            <UiInput type="tel" placeholder="+1-555-0000" v-bind="componentField" />
          </UiFormControl>
          <UiFormDescription />
          <UiFormMessage />
        </UiFormItem>
      </FormField>
      <FormField v-slot="{ componentField }" name="industry" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Industry</UiFormLabel>
          <UiFormControl>
            <UiSelect v-bind="componentField">
              <UiSelectTrigger class="w-[180px]">
                <UiSelectValue placeholder="Select an industry" />
              </UiSelectTrigger>
              <UiSelectContent>
                <UiSelectGroup>
                  <UiSelectItem
                    v-for="industry in industries"
                    :key="industry.value"
                    :value="industry.value"
                  >
                    {{ industry.label }}
                  </UiSelectItem>
                </UiSelectGroup>
              </UiSelectContent>
            </UiSelect>
          </UiFormControl>
          <UiFormDescription />
          <UiFormMessage />
        </UiFormItem>
      </FormField>
      <FormField v-slot="{ componentField }" name="status" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Status</UiFormLabel>
          <UiFormControl>
            <UiSelect v-bind="componentField">
              <UiSelectTrigger class="w-[180px]">
                <UiSelectValue placeholder="Select a status" />
              </UiSelectTrigger>
              <UiSelectContent>
                <UiSelectGroup>
                  <UiSelectItem
                    v-for="status in statuses"
                    :key="status.value"
                    :value="status.value"
                  >
                    <div class="flex items-center gap-2">
                      <component :is="status.icon" class="size-4 shrink-0" />
                      {{ status.label }}
                    </div>
                  </UiSelectItem>
                </UiSelectGroup>
              </UiSelectContent>
            </UiSelect>
          </UiFormControl>
          <UiFormDescription />
          <UiFormMessage />
        </UiFormItem>
      </FormField>
      <FormField v-slot="{ componentField }" name="employees" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Employees</UiFormLabel>
          <UiFormControl>
            <UiRadioGroup class="flex flex-col space-y-1" v-bind="componentField">
              <UiFormItem
                v-for="size in employeeSizes"
                :key="size.value"
                class="flex items-center space-y-0 gap-x-3"
              >
                <UiFormControl>
                  <UiRadioGroupItem :value="size.value" />
                </UiFormControl>
                <UiFormLabel class="font-normal">
                  {{ size.label }}
                </UiFormLabel>
              </UiFormItem>
            </UiRadioGroup>
          </UiFormControl>
          <UiFormDescription />
          <UiFormMessage />
        </UiFormItem>
      </FormField>

      <UiButton type="submit"> Submit </UiButton>
    </form>
  </div>
</template>

