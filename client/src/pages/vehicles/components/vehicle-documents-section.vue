<script setup lang="ts">
import { EyeIcon } from '@/composables/use-icons.composable'

// TODO: Replace with real documents data from vehicle.documents when backend is ready
const documents = [
  {
    id: 1,
    name: 'Registration Certificate.pdf',
    uploadedAt: 'Jan 12, 2025',
    size: '240 KB',
  },
  {
    id: 2,
    name: 'Insurance Policy.pdf',
    uploadedAt: 'Feb 03, 2025',
    size: '320 KB',
  },
  {
    id: 3,
    name: 'Inspection Report.pdf',
    uploadedAt: 'Mar 18, 2025',
    size: '410 KB',
  },
]

function handleViewDocument(documentId: number) {
  // TODO: Implement document viewing logic
  console.log('View document:', documentId)
}
</script>

<template>
  <div class="space-y-2 text-sm">
    <div
      v-for="(document, index) in documents"
      :key="document.id"
      :class="[
        'flex items-center justify-between',
        index > 0 && 'pt-2 border-t border-border',
      ]"
    >
      <div>
        <div class="font-medium">
          {{ document.name }}
        </div>
        <div class="text-xs text-muted-foreground">
          Uploaded {{ document.uploadedAt }} · {{ document.size }}
        </div>
      </div>
      <button
        type="button"
        class="inline-flex h-7 w-7 items-center justify-center rounded-full border border-border text-muted-foreground hover:text-foreground hover:border-foreground transition-colors"
        @click="handleViewDocument(document.id)"
      >
        <EyeIcon class="h-3.5 w-3.5" />
        <span class="sr-only">View document</span>
      </button>
    </div>
  </div>
</template>
