<script lang="ts" setup>
import type { ITeam } from '@/pages/teams/models/teams'

import { Dialog, DialogContent } from '@/components/ui/dialog'

import TeamResourceDialog from './team-resource-dialog.vue'

interface ITeamEditDialogProps {
  team: ITeam
  open: boolean
}

const props = defineProps<ITeamEditDialogProps>()

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
      <TeamResourceDialog :team="props.team" @close="handleClose" />
    </DialogContent>
  </Dialog>
</template>
