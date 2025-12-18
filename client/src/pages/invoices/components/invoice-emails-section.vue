<script setup lang="ts">
import { CheckCircle2, Clock, Mail, XCircle } from 'lucide-vue-next'
import { ref } from 'vue'

import type { IInvoiceEmail } from '@/services/invoices.service'

import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'

import { formatDateTime } from '../utils/formatters'

interface IProps {
  emails: IInvoiceEmail[]
}

const props = defineProps<IProps>()

const expandedEmails = ref<Set<number>>(new Set())

function toggleEmail(id: number) {
  if (expandedEmails.value.has(id)) {
    expandedEmails.value.delete(id)
  } else {
    expandedEmails.value.add(id)
  }
}

function getStatusIcon(status: string) {
  switch (status) {
    case 'sent':
      return CheckCircle2
    case 'failed':
      return XCircle
    case 'pending':
      return Clock
    default:
      return Mail
  }
}
</script>

<template>
  <Card>
    <CardHeader class="pb-4">
      <div class="flex items-center gap-2">
        <Mail class="size-4 text-muted-foreground" />
        <CardTitle class="text-base font-semibold"> Sent Emails </CardTitle>
      </div>
    </CardHeader>
    <CardContent>
      <div v-if="props.emails.length > 0" class="space-y-4">
        <div v-for="email in props.emails" :key="email.id" class="space-y-2">
          <div class="flex items-start justify-between gap-2">
            <div class="flex-1 space-y-1">
              <div class="flex items-center gap-2">
                <Badge
                  :class="`text-xs ${email.status_formatted.style || 'bg-gray-100 text-gray-800'}`"
                >
                  <component :is="getStatusIcon(email.status)" class="size-3 mr-1" />
                  {{ email.status_formatted.label }}
                </Badge>
                <span class="text-xs text-muted-foreground">
                  {{ formatDateTime(email.sent_at || email.created_at) }}
                </span>
              </div>
              <p class="text-sm font-medium text-foreground">
                {{ email.subject }}
              </p>
              <p class="text-xs text-muted-foreground">To: {{ email.to }}</p>
            </div>
          </div>
          <div
            v-if="email.opened_at || email.clicked_at"
            class="flex items-center gap-4 text-xs text-muted-foreground"
          >
            <span v-if="email.opened_at"> Opened: {{ formatDateTime(email.opened_at) }} </span>
            <span v-if="email.clicked_at"> Clicked: {{ formatDateTime(email.clicked_at) }} </span>
          </div>
          <div
            v-if="email.error_message"
            class="rounded-md bg-destructive/10 p-2 text-xs text-destructive"
          >
            {{ email.error_message }}
          </div>
          <div v-if="email.body" class="mt-2">
            <Button
              variant="ghost"
              size="sm"
              class="h-auto p-0 text-xs"
              @click="toggleEmail(email.id)"
            >
              {{ expandedEmails.has(email.id) ? 'Hide' : 'Show' }} preview
            </Button>
            <div
              v-if="expandedEmails.has(email.id)"
              class="mt-2 rounded-md bg-muted/50 p-3 text-xs text-muted-foreground whitespace-pre-wrap"
            >
              {{ email.body }}
            </div>
          </div>
          <Separator v-if="email !== props.emails[props.emails.length - 1]" />
        </div>
      </div>
      <div v-else class="py-8 text-center">
        <div class="inline-flex items-center justify-center size-10 rounded-full bg-muted mb-3">
          <Mail class="size-5 text-muted-foreground" />
        </div>
        <p class="text-sm font-medium text-muted-foreground">No emails sent</p>
        <p class="text-xs text-muted-foreground mt-1">Sent emails will appear here.</p>
      </div>
    </CardContent>
  </Card>
</template>
