<script setup lang="ts">
import type { IEquipment } from '@/pages/equipments/models/equipments'

import { useEquipments } from '@/pages/equipments/composables/use-equipments.composable'

interface IEquipmentDeleteProps {
  equipment: IEquipment
}

const props = defineProps<IEquipmentDeleteProps>()

const emits = defineEmits<{
  close: []
}>()

const { deleteEquipments } = useEquipments()

async function handleRemove() {
  await deleteEquipments(props.equipment.id)
  emits('close')
}
</script>

<template>
  <div>
    <UiDialogTitle>
      Delete this equipment: {{ equipment.name }}?
    </UiDialogTitle>
    <UiDialogDescription class="mt-2 font-medium">
      You are about to delete an equipment with the ID {{ equipment.id }}. This
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
