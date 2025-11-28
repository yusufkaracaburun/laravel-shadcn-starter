import type { AxiosError } from 'axios'

import { useQuery } from '@tanstack/vue-query'

import { useAxios } from '@/composables/use-axios'

import type { IResponse } from '../types/response.type'

export interface Subscription {
  id: number
  name: string
  stripe_id: string
  stripe_status: string
  stripe_price: string | null
  quantity: number | null
  trial_ends_at: string | null
  ends_at: string | null
  created_at: string
  updated_at: string
}

export interface SubscriptionPlan {
  id: string
  name: string
  price: number
  interval: string
  features: string[]
}

export interface Invoice {
  id: string
  amount: number
  currency: string
  status: string
  date: string
  invoice_pdf: string | null
}

// Get subscriptions
export function useGetSubscriptionsQuery() {
  const { axiosInstance } = useAxios()

  return useQuery<IResponse<Subscription[]>, AxiosError>({
    queryKey: ['subscriptions'],
    queryFn: async () => {
      const response = await axiosInstance.get('/subscriptions')
      return response.data
    },
  })
}

// Get available subscription plans
export function useGetAvailablePlansQuery() {
  const { axiosInstance } = useAxios()

  return useQuery<IResponse<SubscriptionPlan[]>, AxiosError>({
    queryKey: ['subscriptionPlans'],
    queryFn: async () => {
      const response = await axiosInstance.get('/subscriptions/available')
      return response.data
    },
  })
}

// Get invoices
export function useGetInvoicesQuery() {
  const { axiosInstance } = useAxios()

  return useQuery<IResponse<Invoice[]>, AxiosError>({
    queryKey: ['invoices'],
    queryFn: async () => {
      const response = await axiosInstance.get('/subscriptions/invoices')
      return response.data
    },
  })
}

