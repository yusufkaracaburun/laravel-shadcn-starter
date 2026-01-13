<script setup lang="ts">
import { MoreVertical, Package } from 'lucide-vue-next'
import { useRouter } from 'vue-router'

import type { IProduct } from '@/pages/products/models/products'

import { Badge } from '@/components/ui/badge'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

import ProductDelete from './product-delete.vue'
import ProductResourceDialog from './product-resource-dialog.vue'

interface Props {
  product: IProduct
}

const props = defineProps<Props>()
const router = useRouter()

const showComponent = shallowRef<
  typeof ProductResourceDialog | typeof ProductDelete | null
>(null)
const isDialogOpen = ref(false)

type TCommand = 'view' | 'edit' | 'delete'

function handleSelect(command: TCommand) {
  switch (command) {
    case 'view':
      router.push({
        name: '/products/view/[id]',
        params: { id: props.product.id.toString() },
      })
      break
    case 'edit':
      showComponent.value = ProductResourceDialog
      isDialogOpen.value = true
      break
    case 'delete':
      showComponent.value = ProductDelete
      isDialogOpen.value = true
      break
  }
}

// Format price - handles both Money object and number
function formatPrice(
  price: number | { formatted: string } | undefined,
): string {
  if (!price)
    return '$0.00'
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
  return '$0.00'
}
</script>

<template>
  <Card
    class="hover:shadow-md transition-shadow cursor-pointer"
    @click="
      router.push({ name: '/products/view/[id]', params: { id: product.id.toString() } })
    "
  >
    <CardHeader>
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-2">
          <Package class="size-5 text-muted-foreground" />
          <CardTitle class="line-clamp-1">
            {{ product.name }}
          </CardTitle>
        </div>
        <UiDropdownMenu @click.stop>
          <UiDropdownMenuTrigger as-child @click.stop>
            <UiButton variant="ghost" size="icon" class="size-8" @click.stop>
              <MoreVertical class="size-4" />
              <span class="sr-only">Open menu</span>
            </UiButton>
          </UiDropdownMenuTrigger>
          <UiDropdownMenuContent align="end" @click.stop>
            <UiDropdownMenuItem @click.stop="handleSelect('view')">
              View
            </UiDropdownMenuItem>
            <UiDropdownMenuItem @click.stop="handleSelect('edit')">
              Edit
            </UiDropdownMenuItem>
            <UiDropdownMenuItem
              class="text-destructive"
              @click.stop="handleSelect('delete')"
            >
              Delete
            </UiDropdownMenuItem>
          </UiDropdownMenuContent>
        </UiDropdownMenu>
      </div>
      <CardDescription v-if="product.description" class="line-clamp-2">
        {{ product.description }}
      </CardDescription>
    </CardHeader>
    <CardContent>
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-sm text-muted-foreground">Unit Price</span>
          <span class="font-semibold">{{ formatPrice(product.unit_price) }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-sm text-muted-foreground">VAT Rate</span>
          <Badge variant="outline">
            {{ product.vat_rate }}%
          </Badge>
        </div>
        <div v-if="product.unit" class="flex items-center justify-between">
          <span class="text-sm text-muted-foreground">Unit</span>
          <Badge variant="secondary">
            {{ product.unit }}
          </Badge>
        </div>
      </div>
    </CardContent>
    <CardFooter class="text-xs text-muted-foreground">
      Created {{ new Date(product.created_at).toLocaleDateString() }}
    </CardFooter>

    <UiDialog v-model:open="isDialogOpen">
      <UiDialogContent v-if="showComponent" class="sm:max-w-[425px]">
        <component
          :is="showComponent"
          :product="product"
          @close="isDialogOpen = false"
        />
      </UiDialogContent>
    </UiDialog>
  </Card>
</template>
