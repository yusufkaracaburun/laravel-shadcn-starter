<script lang="ts" setup>
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { toast } from 'vue-sonner'
import { z } from 'zod'

import { FormField } from '@/components/ui/form'

import type { Project } from '../data/schema'

import { categories, priorities, statuses } from '../data/data'

const props = defineProps<{
  project: Project | null
}>()
const emits = defineEmits(['close'])

const formSchema = toTypedSchema(
  z.object({
    name: z
      .string()
      .min(2)
      .max(100)
      .default(props.project?.name ?? ''),
    description: z.string().default(props.project?.description ?? ''),
    status: z.string().default(props.project?.status ?? ''),
    priority: z.string().default(props.project?.priority ?? ''),
    category: z.string().default(props.project?.category ?? ''),
    startDate: z.string().default(props.project?.startDate ?? ''),
    endDate: z.string().default(props.project?.endDate ?? ''),
    progress: z.number().min(0).max(100).default(props.project?.progress ?? 0),
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
            <UiInput type="text" placeholder="Project name" v-bind="componentField" />
          </UiFormControl>
          <UiFormDescription />
          <UiFormMessage />
        </UiFormItem>
      </FormField>
      <FormField v-slot="{ componentField }" name="description" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Description</UiFormLabel>
          <UiFormControl>
            <UiTextarea placeholder="Project description" v-bind="componentField" />
          </UiFormControl>
          <UiFormDescription />
          <UiFormMessage />
        </UiFormItem>
      </FormField>
      <FormField v-slot="{ componentField }" name="category" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Category</UiFormLabel>
          <UiFormControl>
            <UiSelect v-bind="componentField">
              <UiSelectTrigger class="w-[180px]">
                <UiSelectValue placeholder="Select a category" />
              </UiSelectTrigger>
              <UiSelectContent>
                <UiSelectGroup>
                  <UiSelectItem
                    v-for="category in categories"
                    :key="category.value"
                    :value="category.value"
                  >
                    {{ category.label }}
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
      <FormField v-slot="{ componentField }" name="priority" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Priority</UiFormLabel>
          <UiFormControl>
            <UiSelect v-bind="componentField">
              <UiSelectTrigger class="w-[180px]">
                <UiSelectValue placeholder="Select a priority" />
              </UiSelectTrigger>
              <UiSelectContent>
                <UiSelectGroup>
                  <UiSelectItem
                    v-for="priority in priorities"
                    :key="priority.value"
                    :value="priority.value"
                  >
                    <div class="flex items-center gap-2">
                      <component :is="priority.icon" class="size-4 shrink-0" />
                      {{ priority.label }}
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
      <FormField v-slot="{ componentField }" name="startDate" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Start Date</UiFormLabel>
          <UiFormControl>
            <UiInput type="date" v-bind="componentField" />
          </UiFormControl>
          <UiFormDescription />
          <UiFormMessage />
        </UiFormItem>
      </FormField>
      <FormField v-slot="{ componentField }" name="endDate" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>End Date</UiFormLabel>
          <UiFormControl>
            <UiInput type="date" v-bind="componentField" />
          </UiFormControl>
          <UiFormDescription />
          <UiFormMessage />
        </UiFormItem>
      </FormField>
      <FormField v-slot="{ componentField }" name="progress" :validate-on-blur="!isFieldDirty">
        <UiFormItem>
          <UiFormLabel>Progress (%)</UiFormLabel>
          <UiFormControl>
            <UiInput type="number" min="0" max="100" v-bind="componentField" />
          </UiFormControl>
          <UiFormDescription />
          <UiFormMessage />
        </UiFormItem>
      </FormField>

      <UiButton type="submit"> Submit </UiButton>
    </form>
  </div>
</template>

