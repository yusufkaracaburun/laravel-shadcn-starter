<script lang="ts" setup>
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogTrigger } from '@/components/ui/dialog'
import { Building2Icon } from '@/composables/use-icons.composable'

import CompanyResourceDialog from './company-resource-dialog.vue'

const isOpen = ref(false)
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button>
        <Building2Icon />
        Create Company
      </Button>
    </DialogTrigger>
    <DialogContent
      class="top-10 right-10 bottom-10 left-auto translate-x-0 translate-y-0 sm:max-w-[425px]"
    >
      <CompanyResourceDialog :company="null" @close="isOpen = false" />
    </DialogContent>
  </Dialog>
</template>
