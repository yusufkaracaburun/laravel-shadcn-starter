<script setup lang="ts">
import type { ITeam } from '@/pages/teams/models/teams'

import { useTeams } from '@/pages/teams/composables/use-teams.composable'

interface ITeamDeleteProps {
  team: ITeam
}

const props = defineProps<ITeamDeleteProps>()

const emits = defineEmits<{
  close: []
}>()

const { deleteTeam } = useTeams()

async function handleRemove() {
  await deleteTeam(props.team.id)
  emits('close')
}
</script>

<template>
  <div>
    <UiDialogTitle>
      Delete this team: {{ team.name }}?
    </UiDialogTitle>
    <UiDialogDescription class="mt-2 font-medium">
      You are about to delete a team with the ID {{ team.id }}. This
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
