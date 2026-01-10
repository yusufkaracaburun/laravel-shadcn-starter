<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import { computed, ref } from 'vue'

import type { IItem } from '@/pages/items/models/items'

import Page from '@/components/global-layout/basic-page.vue'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import {
  LayoutGridIcon,
  PackageIcon,
} from '@/composables/use-icons.composable'
import { useItems } from '@/composables/use-items.composable'

import ItemDetailsCard from './components/item-details-card.vue'
import ItemHeader from './components/item-header.vue'
import ItemNavbar from './components/item-navbar.vue'
import ItemProfileCard from './components/item-profile-card.vue'
import ItemViewLayout from './components/item-view-layout.vue'

// Composables
const {
  itemByIdResponse,
  isLoadingItemById,
  isErrorItemById,
  errorItemById,
  fetchItemByIdData,
} = useItems()

// Computed properties
const item = computed<IItem | null>(() => {
  const data = itemByIdResponse.value?.data
  // Handle case where data might be an array (shouldn't happen, but type safety)
  if (Array.isArray(data)) {
    return data[0] ?? null
  }
  return data ?? null
})

const pageTitle = computed(() => item.value?.name ?? 'Item Details')

const pageDescription = computed(() =>
  item.value
    ? `View details for ${item.value.name}`
    : 'Loading item information...',
)

const formattedCreatedAt = computed(() => {
  if (!item.value?.created_at) {
    return '—'
  }

  return new Date(item.value.created_at).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
})

const formattedUpdatedAt = computed(() => {
  if (!item.value?.updated_at) {
    return '—'
  }

  return new Date(item.value.updated_at).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
})

// Tab state
const activeTab = ref('overview')

// Event handlers
function handleEditClosed() {
  fetchItemByIdData()
}

function handleDeleteClosed() {
  // Item will be redirected by the navbar component
}
</script>

<template>
  <Page :title="pageTitle" :description="pageDescription">
    <template #actions>
      <ItemNavbar
        v-if="item"
        :item="item"
        @edit-closed="handleEditClosed"
        @delete-closed="handleDeleteClosed"
      />
    </template>

    <ItemViewLayout
      :is-loading="isLoadingItemById"
      :is-error="isErrorItemById"
      :error-object="errorItemById"
      :on-retry="fetchItemByIdData"
    >
      <template v-if="item">
        <div class="space-y-8">
          <!-- Enhanced Header Section -->
          <div
            class="relative overflow-hidden rounded-xl border bg-gradient-to-br from-background to-muted/20 p-8 shadow-sm"
          >
            <div class="relative z-10">
              <ItemHeader :item="item" />
            </div>
          </div>

          <!-- Modern Tabs Section -->
          <div class="space-y-6">
            <Tabs v-model="activeTab" class="w-full">
              <TabsList
                class="h-auto w-full justify-start gap-1 bg-muted/50 p-1"
              >
                <TabsTrigger
                  value="overview"
                  class="gap-2 rounded-md px-4 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
                >
                  <LayoutGridIcon class="size-4" />
                  <span>Overview</span>
                </TabsTrigger>
                <TabsTrigger
                  value="details"
                  class="gap-2 rounded-md px-4 py-2.5 data-[state=active]:bg-background data-[state=active]:shadow-sm"
                >
                  <PackageIcon class="size-4" />
                  <span>Details</span>
                </TabsTrigger>
              </TabsList>

              <TabsContent value="overview" class="mt-8">
                <div class="grid gap-6 lg:grid-cols-2">
                  <ItemProfileCard :item="item" />

                  <ItemDetailsCard
                    :item="item"
                    :created-at="formattedCreatedAt"
                    :updated-at="formattedUpdatedAt"
                  />
                </div>
              </TabsContent>

              <TabsContent value="details" class="mt-8">
                <div class="max-w-2xl">
                  <ItemDetailsCard
                    :item="item"
                    :created-at="formattedCreatedAt"
                    :updated-at="formattedUpdatedAt"
                  />
                </div>
              </TabsContent>
            </Tabs>
          </div>
        </div>
      </template>
    </ItemViewLayout>
  </Page>
</template>
