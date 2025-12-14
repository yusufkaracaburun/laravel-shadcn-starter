<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import { ArrowLeft, Package, FilePenLine, Trash2 } from 'lucide-vue-next'
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import type { Item } from './data/schema'

import Error from '@/components/custom-error.vue'
import Page from '@/components/global-layout/basic-page.vue'
import Loading from '@/components/loading.vue'
import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { useGetItemQuery } from '@/services/items.service'

import ItemDelete from './components/item-delete.vue'
import ItemResourceDialog from './components/item-resource-dialog.vue'

const route = useRoute()
const router = useRouter()

const itemId = computed(() => Number(route.params.id))

const { data: itemResponse, isLoading, isError, error, refetch } = useGetItemQuery(itemId)

const item = computed<Item | null>(() => itemResponse.value?.data ?? null)

const showComponent = shallowRef<typeof ItemResourceDialog | typeof ItemDelete | null>(null)
const isDialogOpen = ref(false)

type TCommand = 'edit' | 'delete'

function handleSelect(command: TCommand) {
  switch (command) {
    case 'edit':
      showComponent.value = ItemResourceDialog
      isDialogOpen.value = true
      break
    case 'delete':
      showComponent.value = ItemDelete
      isDialogOpen.value = true
      break
  }
}

function handleEditClose() {
  isDialogOpen.value = false
  // Refetch item data after edit
  refetch()
}

function handleDeleteClose() {
  isDialogOpen.value = false
  // Navigate back to items list after deletion
  router.push('/items')
}

// Format date
function formatDate(dateString: string | null): string {
  if (!dateString)
    return '—'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

// Format datetime
function formatDateTime(dateString: string | null): string {
  if (!dateString)
    return '—'
  return new Date(dateString).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

// Format price - handles both Money object and number
function formatPrice(price: number | { formatted: string } | undefined): string {
  if (!price) return '—'
  // Handle Money object from backend
  if (typeof price === 'object' && 'formatted' in price) {
    return price.formatted
  }
  // Handle number (backward compatibility)
  if (typeof price === 'number') {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
    }).format(price)
  }
  return '—'
}
</script>

<template>
  <Page title="Item Details" description="View and manage item information" sticky>
    <template #actions>
      <div class="flex items-center gap-2">
        <Button variant="outline" size="sm" @click="router.back()">
          <ArrowLeft class="mr-2 size-4" />
          Back
        </Button>
        <Button
          v-if="item"
          variant="outline"
          size="sm"
          @click="handleSelect('edit')"
        >
          <FilePenLine class="mr-2 size-4" />
          Edit
        </Button>
        <Button
          v-if="item"
          variant="destructive"
          size="sm"
          @click="handleSelect('delete')"
        >
          <Trash2 class="mr-2 size-4" />
          Delete
        </Button>
      </div>
    </template>

    <div v-if="isLoading" class="flex items-center justify-center min-h-[400px]">
      <Loading />
    </div>

    <Error
      v-else-if="isError"
      :error="error"
      :retry="refetch"
      title="Failed to load item"
      description="We couldn't load the item details. Please try again."
    />

    <div v-else-if="item" class="space-y-6">
      <Card>
        <CardHeader>
          <div class="flex items-center gap-2">
            <Package class="size-5 text-muted-foreground" />
            <CardTitle>{{ item.name }}</CardTitle>
          </div>
          <CardDescription v-if="item.description">
            {{ item.description }}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <p class="text-sm font-medium text-muted-foreground">Unit Price</p>
              <p class="text-lg font-semibold">{{ formatPrice(item.unit_price) }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">VAT Rate</p>
              <p class="text-lg font-semibold">{{ item.vat_rate }}%</p>
            </div>
            <div v-if="item.unit">
              <p class="text-sm font-medium text-muted-foreground">Unit</p>
              <p class="text-lg font-semibold">{{ item.unit }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">Item ID</p>
              <p class="text-lg font-semibold">#{{ item.id }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Timestamps</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <p class="text-sm font-medium text-muted-foreground">Created At</p>
              <p class="text-sm">{{ formatDateTime(item.created_at) }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-muted-foreground">Updated At</p>
              <p class="text-sm">{{ formatDateTime(item.updated_at) }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <UiDialog v-model:open="isDialogOpen">
      <UiDialogContent v-if="showComponent && item" class="sm:max-w-[425px]">
        <ItemResourceDialog
          v-if="showComponent === ItemResourceDialog"
          :item="item"
          @close="handleEditClose"
        />
        <ItemDelete
          v-else-if="showComponent === ItemDelete"
          :item="item"
          @close="handleDeleteClose"
        />
      </UiDialogContent>
    </UiDialog>
  </Page>
</template>

