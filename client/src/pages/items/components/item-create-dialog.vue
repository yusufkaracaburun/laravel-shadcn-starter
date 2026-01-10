<script lang="ts" setup>
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogTrigger } from '@/components/ui/dialog'
import { PackagePlus } from 'lucide-vue-next'

import ItemResourceDialog from './item-resource-dialog.vue'

const isOpen = ref(false)
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button>
        <PackagePlus class="mr-2 size-4" />
        Create Item
      </Button>
    </DialogTrigger>
    <DialogContent
      class="top-5 right-5 bottom-5 left-auto translate-x-0 translate-y-0 sm:max-w-[425px]"
    >
      <ItemResourceDialog :item="null" @close="isOpen = false" />
    </DialogContent>
  </Dialog>
</template>
