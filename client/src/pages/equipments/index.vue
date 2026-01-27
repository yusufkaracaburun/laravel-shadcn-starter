<script setup lang="ts">
import { onMounted, ref } from 'vue'

import type { IEquipment } from '@/pages/equipments/models/equipments'

import Page from '@/components/global-layout/basic-page-with-list.vue'
import { useEquipments } from '@/pages/equipments/composables/use-equipments.composable'

import EquipmentDetail from './components/equipment-detail.vue'
import EquipmentList from './components/equipment-list.vue'
import EquipmentsCreate from './components/equipments-create-dialog.vue'

const {
  loading,
  equipments,
  filter,
  sort,
  onFiltersChange,
  onSortingChange,
  fetchEquipmentsPrerequisitesData,
  fetchEquipmentsData,
} = useEquipments()

const selectedEquipment = ref<IEquipment | null>(null)

onMounted(() => {
  fetchEquipmentsPrerequisitesData()
  fetchEquipmentsData()
})
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
        :filter="filter"
        :sort="sort"
        @update:selected-equipment="selectedEquipment = $event"
        @filters-change="onFiltersChange"
        @sort-change="onSortingChange"
      />
    </template>

    <template #detail>
      <Transition name="fade-slide" mode="out-in">
        <EquipmentDetail
          :key="selectedEquipment?.id || 'no-equipment'"
          :equipment="selectedEquipment"
        />
      </Transition>
    </template>
  </Page>
</template>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition:
    opacity 0.2s ease,
    transform 0.2s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(4px);
}

.fade-slide-enter-to,
.fade-slide-leave-from {
  opacity: 1;
  transform: translateX(0);
}
</style>
