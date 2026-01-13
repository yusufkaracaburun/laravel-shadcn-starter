<script setup lang="ts">
import type { ComputedRef } from 'vue'

import { computed } from 'vue'

import type {
  IInvoice,
  IInvoiceActivity,
  IInvoiceEmail,
  IInvoicePayment,
} from '@/pages/invoices/models/invoice'

import { Accordion } from '@/components/ui/accordion'
import { INVOICE_STATUSES } from '@/pages/invoices/data/data'
import InvoiceActivityTimeline from '@/pages/invoices/view/components/invoice-activity-timeline.vue'
import InvoiceEmails from '@/pages/invoices/view/components/invoice-emails.vue'
import InvoicePayments from '@/pages/invoices/view/components/invoice-payments.vue'

const props = defineProps<{
  invoice: IInvoice
}>()

const invoiceActivities = computed(
  () => props.invoice.activities ?? [],
) as ComputedRef<IInvoiceActivity[]>
const invoicePayments = computed(
  () => props.invoice.payments ?? [],
) as ComputedRef<IInvoicePayment[]>
const invoiceEmails = computed(() => props.invoice.emails ?? []) as ComputedRef<
  IInvoiceEmail[]
>

const steps = computed(() => {
  const currentStatusIndex = INVOICE_STATUSES.findIndex(
    s => s.value === props.invoice.status,
  )

  return INVOICE_STATUSES.map((status, index) => ({
    step: status.id,
    title: status.label,
    description: status.description,
    icon: status.icon,
    state: index <= currentStatusIndex ? 'completed' : 'inactive',
  }))
})
</script>

<template>
  <div class="print:max-w-none print:mx-0 print:p-0 p-4">
    <Accordion
      type="single"
      collapsible
      class="w-full"
      default-value="item-activity"
    >
      <InvoiceActivityTimeline :activities="invoiceActivities" />
      <InvoicePayments :payments="invoicePayments" />
      <InvoiceEmails :emails="invoiceEmails" />
    </Accordion>
    <!-- <Stepper orientation="vertical" class="mx-auto flex w-full max-w-md flex-col justify-start gap-10">
      <StepperItem v-for="step in steps" :key="step.step" v-slot="{ state }"
        class="relative flex w-full items-start gap-6" :step="step.step">
        <StepperSeparator v-if="step.step !== steps[steps.length - 1]?.step"
          class="absolute left-[18px] top-[38px] block h-[105%] w-0.5 shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary" />

        <StepperTrigger as-child>
          <Button :variant="state === 'completed' || state === 'active' ? 'default' : 'outline'" size="icon"
            class="z-10 rounded-full shrink-0"
            :class="[state === 'active' && 'ring-2 ring-ring ring-offset-2 ring-offset-background']">
            <component :is="step.icon" v-if="step.icon" class="size-5" />
          </Button>
        </StepperTrigger>

        <div class="flex flex-col gap-1">
          <StepperTitle :class="[state === 'active' && 'text-primary']"
            class="text-sm font-semibold transition lg:text-base">
            {{ step.title }}
          </StepperTitle>
          <StepperDescription :class="[state === 'active' && 'text-primary']"
            class="sr-only text-xs text-muted-foreground transition md:not-sr-only lg:text-sm">
            {{ step.description }}
          </StepperDescription>
        </div>
      </StepperItem>
    </Stepper> -->
  </div>
</template>
