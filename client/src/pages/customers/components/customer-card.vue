<script setup lang="ts">
import { MoreVertical, User } from 'lucide-vue-next'
import { useRouter } from 'vue-router'

import type { Customer } from '@/services/customers.service'

import { Badge } from '@/components/ui/badge'
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

import CustomerDelete from './customer-delete.vue'
import CustomerResourceDialog from './customer-resource-dialog.vue'

interface Props {
  customer: Customer
}

const props = defineProps<Props>()
const router = useRouter()

const showComponent = shallowRef<
  typeof CustomerResourceDialog | typeof CustomerDelete | null
>(null)
const isDialogOpen = ref(false)

type TCommand = 'view' | 'edit' | 'delete'

function handleSelect(command: TCommand) {
  switch (command) {
    case 'view':
      router.push({
        name: '/customers/[id]',
        params: { id: props.customer.id.toString() },
      })
      break
    case 'edit':
      showComponent.value = CustomerResourceDialog
      isDialogOpen.value = true
      break
    case 'delete':
      showComponent.value = CustomerDelete
      isDialogOpen.value = true
      break
  }
}
</script>

<template>
  <Card
    class="hover:shadow-md transition-shadow cursor-pointer"
    @click="
      router.push({
        name: '/customers/[id]',
        params: { id: customer.id.toString() },
      })
    "
  >
    <CardHeader>
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-2">
          <User class="size-5 text-muted-foreground" />
          <CardTitle class="line-clamp-1">
            {{ customer.name }}
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
      <div class="flex items-center gap-2 mt-2">
        <Badge
          :variant="customer.type === 'business' ? 'default' : 'secondary'"
        >
          {{ customer.type === 'business' ? 'Business' : 'Private' }}
        </Badge>
      </div>
    </CardHeader>
    <CardContent>
      <div class="space-y-2">
        <div v-if="customer.email" class="flex items-center justify-between">
          <span class="text-sm text-muted-foreground">Email</span>
          <span class="text-sm font-medium truncate max-w-[150px]">{{
            customer.email
          }}</span>
        </div>
        <div v-if="customer.phone" class="flex items-center justify-between">
          <span class="text-sm text-muted-foreground">Phone</span>
          <span class="text-sm font-medium">{{ customer.phone }}</span>
        </div>
        <div v-if="customer.city" class="flex items-center justify-between">
          <span class="text-sm text-muted-foreground">City</span>
          <span class="text-sm font-medium">{{ customer.city }}</span>
        </div>
        <div
          v-if="customer.primary_contact"
          class="flex items-center justify-between"
        >
          <span class="text-sm text-muted-foreground">Primary Contact</span>
          <span class="text-sm font-medium truncate max-w-[150px]">{{
            customer.primary_contact.name || customer.primary_contact.email
          }}</span>
        </div>
      </div>
    </CardContent>
    <CardFooter class="text-xs text-muted-foreground">
      Created {{ new Date(customer.created_at).toLocaleDateString() }}
    </CardFooter>

    <UiDialog v-model:open="isDialogOpen">
      <UiDialogContent v-if="showComponent" class="sm:max-w-[425px]">
        <component
          :is="showComponent"
          :customer="customer"
          @close="isDialogOpen = false"
        />
      </UiDialogContent>
    </UiDialog>
  </Card>
</template>
