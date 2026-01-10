<script lang="ts" setup>
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogTrigger } from '@/components/ui/dialog'
import { UserRoundPlusIcon } from '@/composables/use-icons.composable'

import UserResourceDialog from './user-resource-dialog.vue'

const isOpen = ref(false)
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button>
        <UserRoundPlusIcon />
        Create User
      </Button>
    </DialogTrigger>
    <DialogContent
      class="top-10 right-10 bottom-10 left-auto translate-x-0 translate-y-0 sm:max-w-[425px]"
    >
      <UserResourceDialog :user="null" @close="isOpen = false" />
    </DialogContent>
  </Dialog>
</template>
