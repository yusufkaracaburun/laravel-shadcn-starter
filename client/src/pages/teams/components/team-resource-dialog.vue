<script lang="ts" setup>
import type { ITeam } from '@/pages/teams/models/teams'

import TeamForm from './team-form.vue'

interface ITeamResourceDialogProps {
  team: ITeam | null
}

const props = defineProps<ITeamResourceDialogProps>()

const emits = defineEmits<{
  close: []
}>()

const title = computed(() => (props.team ? 'Edit Team' : 'New Team'))
const description = computed(() =>
  props.team
    ? `Update team information for ${props.team.name}.`
    : 'Add a new team to the system. Fill in the required information below.',
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
    <TeamForm :team="team" class="mt-2" @close="emits('close')" />
  </div>
</template>
