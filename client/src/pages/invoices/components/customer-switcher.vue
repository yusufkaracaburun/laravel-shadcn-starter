<script lang="ts" setup>
import type { Customer } from '@/services/customers.service'

import {
  Building2Icon,
  ChevronsUpDownIcon,
  PlusIcon,
  UserIcon,
} from '@/composables/use-icons'

interface IProps {
  customers: Customer[]
  selectedCustomerId?: number
  onSelect: (customerId: number) => void
}

const props = defineProps<IProps>()

const selectedCustomer = computed(() => {
  if (!props.selectedCustomerId) return null
  return props.customers.find((c) => c.id === props.selectedCustomerId) || null
})

const isOpen = ref(false)
const showComponent = shallowRef<Component | null>(null)
type TComponent = 'customer-add'

function handleSelect(customer: Customer) {
  props.onSelect(customer.id)
  isOpen.value = false
}

function getCustomerIcon(customer: Customer) {
  return customer.type === 'business' ? Building2Icon : UserIcon
}

function handleSelectCommand(command: TComponent) {
  switch (command) {
    case 'customer-add':
      showComponent.value = defineAsyncComponent(
        () => import('./customer-add.vue'),
      )
      break
  }
}

function handleCustomerCreated() {
  // Refresh customers list - parent component should handle this
  // For now, just close the dialog
  isOpen.value = false
}
</script>

<template>
  <UiDialog v-model:open="isOpen">
    <UiDropdownMenu>
      <UiDropdownMenuTrigger as-child>
        <UiButton
          variant="outline"
          class="w-full justify-start h-12 px-3 data-[state=open]:bg-accent data-[state=open]:text-accent-foreground"
          :class="{
            'text-muted-foreground': !selectedCustomer,
          }"
        >
          <div
            v-if="selectedCustomer"
            class="flex items-center justify-center rounded-lg aspect-square size-8 bg-primary text-primary-foreground shrink-0 mr-2"
          >
            <component :is="getCustomerIcon(selectedCustomer)" class="size-4" />
          </div>
          <div
            v-else
            class="flex items-center justify-center rounded-lg aspect-square size-8 bg-muted shrink-0 mr-2"
          >
            <UserIcon class="size-4 text-muted-foreground" />
          </div>
          <div class="grid flex-1 text-sm leading-tight text-left">
            <span v-if="selectedCustomer" class="font-semibold truncate">
              {{ selectedCustomer.name }}
            </span>
            <span v-else class="text-muted-foreground">Select a customer</span>
            <span
              v-if="selectedCustomer?.type"
              class="text-xs truncate capitalize text-muted-foreground"
            >
              {{ selectedCustomer.type }}
            </span>
          </div>
          <ChevronsUpDownIcon
            class="ml-auto size-4 text-muted-foreground shrink-0 opacity-50"
          />
        </UiButton>
      </UiDropdownMenuTrigger>
      <UiDropdownMenuContent
        class="w-(--radix-dropdown-menu-trigger-width) min-w-75 rounded-lg max-h-[300px] overflow-y-auto"
        align="start"
        :side-offset="4"
      >
        <UiDropdownMenuLabel class="text-xs text-muted-foreground">
          Customers
        </UiDropdownMenuLabel>
        <template v-if="customers.length > 0">
          <UiDropdownMenuItem
            v-for="customer in customers"
            :key="customer.id"
            class="gap-2 p-2"
            @click="handleSelect(customer)"
          >
            <div
              class="flex items-center justify-center border rounded-sm size-6 shrink-0"
            >
              <component
                :is="getCustomerIcon(customer)"
                class="size-4 shrink-0"
              />
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-medium truncate">
                {{ customer.name }}
              </div>
              <div
                v-if="customer.type"
                class="text-xs text-muted-foreground capitalize"
              >
                {{ customer.type }}
              </div>
              <div
                v-if="customer.primary_contact?.name"
                class="text-xs text-muted-foreground truncate"
              >
                Contact: {{ customer.primary_contact.name }}
              </div>
            </div>
          </UiDropdownMenuItem>
        </template>
        <div v-else class="px-2 py-4 text-sm text-muted-foreground text-center">
          No customers available
        </div>
        <UiDropdownMenuSeparator />

        <UiDialogTrigger as-child>
          <UiDropdownMenuItem
            class="gap-2 p-2"
            @click.stop="handleSelectCommand('customer-add')"
          >
            <div
              class="flex items-center justify-center border rounded-md size-6 bg-background"
            >
              <PlusIcon class="size-4" />
            </div>
            <div class="font-medium text-muted-foreground">Add customer</div>
          </UiDropdownMenuItem>
        </UiDialogTrigger>
      </UiDropdownMenuContent>
    </UiDropdownMenu>

    <UiDialogContent>
      <component :is="showComponent" @close="handleCustomerCreated" />
    </UiDialogContent>
  </UiDialog>
</template>
