import type { AxiosError } from 'axios'

import { useQuery } from '@tanstack/vue-query'

import { useAxios } from '@/composables/use-axios'

import type { IResponse } from '../types/response.type'

export interface ChatMessage {
  id: number
  role: 'user' | 'assistant' | 'system'
  content: string
  created_at: string
}

export interface ChatData {
  messages: ChatMessage[]
  model: string
  temperature: number
}

// Get chat data
export function useGetChatDataQuery() {
  const { axiosInstance } = useAxios()

  return useQuery<IResponse<ChatData>, AxiosError>({
    queryKey: ['chatData'],
    queryFn: async () => {
      const response = await axiosInstance.get('/chat/data')
      return response.data
    },
  })
}

