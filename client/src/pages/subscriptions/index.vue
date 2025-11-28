<script setup lang="ts">
import { computed } from 'vue'
import { Icon } from '@iconify/vue'

import { useGetSubscriptionsQuery, useGetAvailablePlansQuery, useGetInvoicesQuery } from '@/services/api/subscriptions.api'
import ActionSection from '@/components/action-section.vue'

const { data: subscriptionsData, isLoading: subscriptionsLoading } = useGetSubscriptionsQuery()
const { data: plansData, isLoading: plansLoading } = useGetAvailablePlansQuery()
const { data: invoicesData, isLoading: invoicesLoading } = useGetInvoicesQuery()

const subscriptions = computed(() => subscriptionsData.value?.data || [])
const plans = computed(() => plansData.value?.data || [])
const invoices = computed(() => invoicesData.value?.data || [])
</script>

<template>
  <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="mb-6">
      <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
        Subscriptions
      </h2>
    </div>

    <div class="space-y-6">
      <ActionSection>
        <template #title>
          Current Subscriptions
        </template>

        <template #description>
          Manage your active subscriptions and billing information.
        </template>

        <template #content>
          <div v-if="subscriptionsLoading" class="text-center py-8">
            Loading subscriptions...
          </div>
          <div v-else-if="subscriptions.length === 0" class="text-center py-8 text-muted-foreground">
            No active subscriptions.
          </div>
          <div v-else class="space-y-4">
            <div
              v-for="subscription in subscriptions"
              :key="subscription.id"
              class="border rounded-lg p-4"
            >
              <div class="flex justify-between items-center">
                <div>
                  <h3 class="font-semibold">{{ subscription.name }}</h3>
                  <p class="text-sm text-muted-foreground">
                    Status: {{ subscription.stripe_status }}
                  </p>
                </div>
                <UiBadge :variant="subscription.stripe_status === 'active' ? 'default' : 'secondary'">
                  {{ subscription.stripe_status }}
                </UiBadge>
              </div>
            </div>
          </div>
        </template>
      </ActionSection>

      <ActionSection>
        <template #title>
          Available Plans
        </template>

        <template #description>
          Choose a subscription plan that fits your needs.
        </template>

        <template #content>
          <div v-if="plansLoading" class="text-center py-8">
            Loading plans...
          </div>
          <div v-else-if="plans.length === 0" class="text-center py-8 text-muted-foreground">
            No plans available.
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div
              v-for="plan in plans"
              :key="plan.id"
              class="border rounded-lg p-6"
            >
              <h3 class="text-xl font-semibold mb-2">{{ plan.name }}</h3>
              <p class="text-2xl font-bold mb-4">
                ${{ plan.price }}<span class="text-sm font-normal">/{{ plan.interval }}</span>
              </p>
              <ul class="space-y-2 mb-4">
                <li
                  v-for="feature in plan.features"
                  :key="feature"
                  class="flex items-center"
                >
                  <Icon icon="lucide:check" class="mr-2 text-green-500" />
                  {{ feature }}
                </li>
              </ul>
              <UiButton class="w-full">
                Subscribe
              </UiButton>
            </div>
          </div>
        </template>
      </ActionSection>

      <ActionSection>
        <template #title>
          Invoices
        </template>

        <template #description>
          View and download your billing invoices.
        </template>

        <template #content>
          <div v-if="invoicesLoading" class="text-center py-8">
            Loading invoices...
          </div>
          <div v-else-if="invoices.length === 0" class="text-center py-8 text-muted-foreground">
            No invoices found.
          </div>
          <div v-else class="space-y-4">
            <div
              v-for="invoice in invoices"
              :key="invoice.id"
              class="border rounded-lg p-4 flex justify-between items-center"
            >
              <div>
                <p class="font-semibold">Invoice #{{ invoice.id }}</p>
                <p class="text-sm text-muted-foreground">
                  {{ invoice.date }} - ${{ invoice.amount }} {{ invoice.currency }}
                </p>
              </div>
              <div class="flex items-center gap-2">
                <UiBadge :variant="invoice.status === 'paid' ? 'default' : 'secondary'">
                  {{ invoice.status }}
                </UiBadge>
                <UiButton
                  v-if="invoice.invoice_pdf"
                  variant="outline"
                  size="sm"
                  as="a"
                  :href="invoice.invoice_pdf"
                  target="_blank"
                >
                  Download
                </UiButton>
              </div>
            </div>
          </div>
        </template>
      </ActionSection>
    </div>
  </div>
</template>

