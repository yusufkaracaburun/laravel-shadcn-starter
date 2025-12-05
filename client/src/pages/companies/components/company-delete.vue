<script lang="ts" setup>
import { toast } from 'vue-sonner'

import type { Company } from '../data/schema'

const props = defineProps<{
  company: Company
}>()

function handleRemove() {
  toast(`The following company has been deleted:`, {
    description: h(
      'pre',
      { class: 'mt-2 w-[340px] rounded-md bg-slate-950 p-4' },
      h('code', { class: 'text-white' }, JSON.stringify(props.company, null, 2)),
    ),
  })
}
</script>

<template>
  <div>
    <UiDialogTitle>
      <UiDialogTitle> Delete this company: {{ company.id }} ? </UiDialogTitle>
      <UiDialogDescription class="mt-2 font-medium">
        You are about to delete a company with the ID {{ company.id }}.This action cannot be undone.
      </UiDialogDescription>
    </UiDialogTitle>
    <UiDialogFooter>
      <UiDialogClose as-child>
        <UiButton variant="outline"> Cancel </UiButton>
      </UiDialogClose>

      <UiDialogClose as-child>
        <UiButton variant="destructive" @click="handleRemove"> Delete </UiButton>
      </UiDialogClose>
    </UiDialogFooter>
  </div>
</template>

