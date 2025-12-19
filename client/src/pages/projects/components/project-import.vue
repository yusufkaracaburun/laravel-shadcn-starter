<script lang="ts" setup>
import { Download, FileDown } from 'lucide-vue-next'
import { toast } from 'vue-sonner'

const isOpen = ref(false)
const file = ref()
const error = ref()

watch(file, () => {
  error.value = null
})
watch(isOpen, () => {
  file.value = null
})

function downloadExampleCSV() {
  const link = document.createElement('a')
  link.href = '/examples/projects-import-example.csv'
  link.download = 'projects-import-example.csv'
  document.body.appendChild(link)
  link.click()
  link.remove()
  toast.success('Example CSV file downloaded')
}

function onSubmit() {
  error.value = null

  if (!file.value) {
    error.value = 'File is required'
    return
  }

  toast('You submitted the following values:', {
    description: h(
      'pre',
      { class: 'mt-2 w-[340px] rounded-md bg-slate-950 p-4' },
      h('code', { class: 'text-white' }, JSON.stringify(file.value, null, 2)),
    ),
  })
  isOpen.value = false
}
</script>

<template>
  <UiDialog v-model:open="isOpen">
    <UiDialogTrigger as-child>
      <UiButton variant="outline">
        Import
        <Download />
      </UiButton>
    </UiDialogTrigger>

    <UiDialogContent>
      <UiDialogHeader>
        <UiDialogTitle>Import Projects</UiDialogTitle>
        <UiDialogDescription>
          Import projects quickly from a CSV file. Download the example file to
          see the correct format.
        </UiDialogDescription>
      </UiDialogHeader>

      <div class="space-y-4">
        <div class="flex items-center justify-between rounded-lg border p-3">
          <div class="space-y-0.5">
            <UiLabel class="text-base font-medium">
              Example CSV File
            </UiLabel>
            <p class="text-sm text-muted-foreground">
              Download a template with the correct format and example data
            </p>
          </div>
          <UiButton variant="outline" size="sm" @click="downloadExampleCSV">
            <FileDown class="mr-2 size-4" />
            Download Example
          </UiButton>
        </div>

        <div class="grid w-full max-w-sm items-center gap-1.5">
          <UiLabel>Select CSV File</UiLabel>
          <UiInput id="file" v-model="file" type="file" accept=".csv" />
          <span v-if="error" class="text-sm text-destructive">{{ error }}</span>
          <p class="text-xs text-muted-foreground">
            Required columns: name, status, category. Optional: description,
            start_date, end_date, progress
          </p>
        </div>
      </div>
      <UiDialogFooter>
        <UiButton variant="secondary" @click="isOpen = false">
          Cancel
        </UiButton>
        <UiButton @click="onSubmit">
          Import
        </UiButton>
      </UiDialogFooter>
    </UiDialogContent>
  </UiDialog>
</template>
