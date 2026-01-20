<script setup lang="ts">
import { ref } from 'vue'

import type { IEquipment } from '@/pages/equipments/models/equipments'

import Page from '@/components/global-layout/basic-page-with-list.vue'
import { useEquipments } from '@/pages/equipments/composables/use-equipments.composable'

import EquipmentDetail from './components/equipment-detail.vue'
import EquipmentList from './components/equipment-list.vue'
import EquipmentsCreate from './components/equipments-create-dialog.vue'

const { loading, equipments } = useEquipments()

const selectedEquipment = ref<IEquipment | null>(null)
</script>

<template>
  <Page
    title="Equipment"
    description="Manage your equipment inventory"
    sticky
    data-testid="equipment_page"
  >
    <template #actions>
      <EquipmentsCreate />
    </template>

    <template #default="{ openDetail }">
      <EquipmentList
        :equipments="equipments ?? []"
        :loading="loading"
        :selected-equipment="selectedEquipment"
        :open-detail="openDetail"
        @update:selected-equipment="selectedEquipment = $event"
      />
    </template>

    <template #detail>
      <EquipmentDetail :equipment="selectedEquipment" />
    </template>
  </Page>
</template>
