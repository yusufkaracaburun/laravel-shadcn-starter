<script setup lang="ts">
import { Clock, User } from 'lucide-vue-next'

import type { IInvoiceActivity } from '@/pages/invoices/models/invoice'

import {
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '@/components/ui/accordion'
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip'
import { formatDateTime } from '@/pages/invoices/utils/formatters'

interface Props {
  activities: IInvoiceActivity[]
}

const props = defineProps<Props>()
</script>

<template>
  <AccordionItem value="item-activity">
    <AccordionTrigger
      class="flex items-center justify-between cursor-pointer select-none"
    >
      <h4 class="text-base font-semibold text-gray-900 flex items-center gap-2">
        <Clock class="w-4 h-4 text-gray-600" />
        Activity Timeline
      </h4>
    </AccordionTrigger>
    <AccordionContent class="pt-2 border-t border-gray-200">
      <div v-if="props.activities.length === 0" class="text-center py-8">
        <div
          class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-200 mb-3"
        >
          <Clock class="w-6 h-6 text-gray-400" />
        </div>
        <p class="text-sm text-gray-500">
          No activity yet
        </p>
      </div>

      <div v-else class="px-1">
        <div
          v-for="activity in props.activities"
          :key="activity.id"
          class="relative flex gap-3 pb-3 last:pb-0"
        >
          <!-- Activity content -->
          <div class="flex-1 min-w-0">
            <div class="bg-white rounded-lg border border-gray-100 p-3">
              <div class="flex justify-between items-start mb-1">
                <span class="text-sm text-gray-900 font-bold mb-1">{{
                  activity.description
                }}</span>
              </div>

              <div
                class="flex justify-between items-start mb-1 text-xs text-gray-500"
              >
                <div class="flex items-center gap-2">
                  <User class="w-3 h-3" />
                  <span>{{ activity.causer?.name || 'System' }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-gray-300">•</span>
                  <span>{{ formatDateTime(activity.created_at) }}</span>
                </div>
              </div>

              <div
                v-if="activity.properties"
                class="mt-2 pt-2 border-t border-gray-100"
              >
                <details
                  class="text-xs rounded-md border border-gray-200 bg-white p-1 mt-2"
                >
                  <summary
                    class="cursor-pointer text-gray-700 hover:text-gray-900 font-medium p-1 -m-1"
                  >
                    View details
                  </summary>
                  <div class="mt-2 space-y-1">
                    <div
                      v-for="(value, key) in activity.properties.attributes"
                      :key="key"
                      class="text-gray-700"
                    >
                      <span class="font-medium">{{ key.replace(/_/g, ' ') }}:</span>
                      <TooltipProvider>
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <span class="ml-1">{{
                              typeof value === 'object'
                                ? JSON.stringify(value)
                                : value
                            }}</span>
                          </TooltipTrigger>
                          <TooltipContent>
                            <p>
                              Old Value:
                              {{ activity.properties.old?.[key] }}
                            </p>
                          </TooltipContent>
                        </Tooltip>
                      </TooltipProvider>
                    </div>
                  </div>
                </details>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AccordionContent>
  </AccordionItem>
</template>
