<script lang="ts" setup>
import type { IEquipment } from '@/pages/equipments/models/equipments'

import EquipmentsForm from './equipments-form.vue'

interface IEquipmentResourceDialogProps {
  equipment: IEquipment | null
}

const props = defineProps<IEquipmentResourceDialogProps>()

const emits = defineEmits<{
  close: []
}>()

const title = computed(() => (props.equipment ? 'Edit Equipment' : 'New Equipment'))
const description = computed(() =>
  props.equipment
    ? `Update equipment information for ${props.equipment.name}.`
    : 'Add a new equipment to the system. Fill in the required information below.',
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
    <EquipmentsForm :equipment="equipment" class="mt-2" @close="emits('close')" />
  </div>
</template>
