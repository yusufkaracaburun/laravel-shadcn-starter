<script lang="ts" setup>
import type { ITimesheet } from '@/pages/timesheets/models/timesheets'

import { Dialog, DialogContent } from '@/components/ui/dialog'

import TimesheetResourceDialog from './timesheet-resource-dialog.vue'

interface ITimesheetEditDialogProps {
  timesheet: ITimesheet
  open: boolean
}

const props = defineProps<ITimesheetEditDialogProps>()

const emits = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'close'): void
}>()

function handleClose() {
  emits('update:open', false)
  emits('close')
}
</script>

<template>
  <Dialog :open="props.open" @update:open="emits('update:open', $event)">
    <DialogContent
      class="top-5 right-5 bottom-5 left-auto translate-x-0 translate-y-0 sm:max-w-[425px]"
    >
      <TimesheetResourceDialog :timesheet="props.timesheet" @close="handleClose" />
    </DialogContent>
  </Dialog>
</template>
