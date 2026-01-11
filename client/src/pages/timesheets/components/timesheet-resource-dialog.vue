<script lang="ts" setup>
import type { ITimesheet } from '@/pages/timesheets/models/timesheets'

import TimesheetForm from './timesheet-form.vue'

interface ITimesheetResourceDialogProps {
  timesheet: ITimesheet | null
}

const props = defineProps<ITimesheetResourceDialogProps>()

const emits = defineEmits<{
  close: []
}>()

const title = computed(() => (props.timesheet ? 'Edit Timesheet' : 'New Timesheet'))
const description = computed(() =>
  props.timesheet
    ? `Update timesheet information for ${props.timesheet.date}.`
    : 'Add a new timesheet entry. Fill in the required information below.',
)
</script>

<template>
  <div>
    <UiDialogHeader>
      <UiDialogTitle>
        {{ title }}
      </UiDialogTitle>
      <UiDialogDescription>
        {{ description }}
      </UiDialogDescription>
    </UiDialogHeader>
    <TimesheetForm :timesheet="timesheet" class="mt-2" @close="emits('close')" />
  </div>
</template>
