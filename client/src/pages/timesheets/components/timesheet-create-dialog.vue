<script lang="ts" setup>
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogTrigger } from '@/components/ui/dialog'
import { ClockIcon } from '@/composables/use-icons.composable'

import TimesheetResourceDialog from './timesheet-resource-dialog.vue'

const isOpen = ref(false)
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button>
        <ClockIcon />
        Create Timesheet
      </Button>
    </DialogTrigger>
    <DialogContent
      class="top-5 right-5 bottom-5 left-auto translate-x-0 translate-y-0 sm:max-w-[425px]"
    >
      <TimesheetResourceDialog :timesheet="null" @close="isOpen = false" />
    </DialogContent>
  </Dialog>
</template>
