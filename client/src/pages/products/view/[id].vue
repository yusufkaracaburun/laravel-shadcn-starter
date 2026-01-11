<route lang="yaml">
meta:
  auth: true
</route>

<script setup lang="ts">
import { computed, ref } from 'vue'

import type { IProduct } from '@/pages/products/models/products'

import Page from '@/components/global-layout/basic-page.vue'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import {
  LayoutGridIcon,
  PackageIcon,
} from '@/composables/use-icons.composable'
import { useProducts } from '@/pages/products/composables/use-products.composable'

import ProductDetailsCard from './components/product-details-card.vue'
import ProductHeader from './components/product-header.vue'
import ProductNavbar from './components/product-navbar.vue'
import ProductProfileCard from './components/product-profile-card.vue'
import ProductViewLayout from './components/product-view-layout.vue'

// Composables
const {
  productByIdResponse,
  isLoadingProductById,
  isErrorProductById,
  errorProductById,
  fetchProductByIdData,
} = useProducts()

// Computed properties
const product = computed<IProduct | null>(() => {
  const data = productByIdResponse.value?.data
  // Handle case where data might be an array (shouldn't happen, but type safety)
  if (Array.isArray(data)) {
    return data[0] ?? null
  }
  return data ?? null
})

const pageTitle = computed(() => product.value?.name ?? 'Product Details')

const pageDescription = computed(() =>
  product.value
    ? `View details for ${product.value.name}`
    : 'Loading product information...',
)

const formattedCreatedAt = computed(() => {
  if (!product.value?.created_at) {
    return '—'
  }

  return new Date(product.value.created_at).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
})

const formattedUpdatedAt = computed(() => {
  if (!product.value?.updated_at) {
    return '—'
  }

  return new Date(product.value.updated_at).toLocaleString('en-US', {
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
  fetchProductByIdData()
}

function handleDeleteClosed() {
  // Product will be redirected by the navbar component
}
</script>

<template>
  <Page :title="pageTitle" :description="pageDescription">
    <template #actions>
      <ProductNavbar
        v-if="product"
        :product="product"
        @edit-closed="handleEditClosed"
        @delete-closed="handleDeleteClosed"
      />
    </template>

    <ProductViewLayout
      :is-loading="isLoadingProductById"
      :is-error="isErrorProductById"
      :error-object="errorProductById"
      :on-retry="fetchProductByIdData"
    >
      <template v-if="product">
        <div class="space-y-8">
          <!-- Enhanced Header Section -->
          <div
            class="relative overflow-hidden rounded-xl border bg-gradient-to-br from-background to-muted/20 p-8 shadow-sm"
          >
            <div class="relative z-10">
              <ProductHeader :product="product" />
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
                  <ProductProfileCard :product="product" />

                  <ProductDetailsCard
                    :product="product"
                    :created-at="formattedCreatedAt"
                    :updated-at="formattedUpdatedAt"
                  />
                </div>
              </TabsContent>

              <TabsContent value="details" class="mt-8">
                <div class="max-w-2xl">
                  <ProductDetailsCard
                    :product="product"
                    :created-at="formattedCreatedAt"
                    :updated-at="formattedUpdatedAt"
                  />
                </div>
              </TabsContent>
            </Tabs>
          </div>
        </div>
      </template>
    </ProductViewLayout>
  </Page>
</template>
