<script setup lang="ts">
import type { ITimesheet } from '@/pages/timesheets/models/timesheets'

import { useTimesheets } from '@/pages/timesheets/composables/use-timesheets.composable'

interface ITimesheetDeleteProps {
  timesheet: ITimesheet
}

const props = defineProps<ITimesheetDeleteProps>()

const emits = defineEmits<{
  close: []
}>()

const { deleteTimesheet } = useTimesheets()

async function handleRemove() {
  await deleteTimesheet(props.timesheet.id)
  emits('close')
}
</script>

<template>
  <div>
    <UiDialogTitle>
      Delete this timesheet: {{ timesheet.date }}?
    </UiDialogTitle>
    <UiDialogDescription class="mt-2 font-medium">
      You are about to delete a timesheet with the ID {{ timesheet.id }}. This
      action cannot be undone.
    </UiDialogDescription>
    <UiDialogFooter>
      <UiDialogClose as-child>
        <UiButton variant="outline"> Cancel </UiButton>
      </UiDialogClose>
      <UiDialogClose as-child>
        <UiButton variant="destructive" @click="handleRemove">
          Delete
        </UiButton>
      </UiDialogClose>
    </UiDialogFooter>
  </div>
</template>
