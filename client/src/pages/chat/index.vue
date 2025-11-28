<script setup lang="ts">
import { ref } from 'vue'
import { useGetChatDataQuery } from '@/services/api/chat.api'

const { data: chatData, isLoading } = useGetChatDataQuery()

const messages = computed(() => chatData.value?.data?.messages || [])
const model = computed(() => chatData.value?.data?.model || 'gpt-3.5-turbo')
const temperature = computed(() => chatData.value?.data?.temperature || 0.7)

const newMessage = ref('')
const sending = ref(false)

async function sendMessage() {
  if (!newMessage.value.trim() || sending.value)
    return

  sending.value = true
  // TODO: Implement send message API call
  // For now, just clear the input
  newMessage.value = ''
  sending.value = false
}
</script>

<template>
  <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="mb-6">
      <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
        Chat
      </h2>
    </div>

    <div class="flex flex-col h-[calc(100vh-12rem)]">
      <div class="flex-1 overflow-y-auto border rounded-lg p-4 mb-4 space-y-4">
        <div v-if="isLoading" class="text-center py-8 text-muted-foreground">
          Loading chat...
        </div>
        <div v-else-if="messages.length === 0" class="text-center py-8 text-muted-foreground">
          No messages yet. Start a conversation!
        </div>
        <div
          v-for="(message, index) in messages"
          :key="index"
          class="flex"
          :class="message.role === 'user' ? 'justify-end' : 'justify-start'"
        >
          <div
            class="max-w-[80%] rounded-lg p-3"
            :class="message.role === 'user' ? 'bg-primary text-primary-foreground' : 'bg-muted'"
          >
            <p class="text-sm">{{ message.content }}</p>
            <p class="text-xs mt-1 opacity-70">
              {{ new Date(message.created_at).toLocaleTimeString() }}
            </p>
          </div>
        </div>
      </div>

      <div class="flex gap-2">
        <UiInput
          v-model="newMessage"
          placeholder="Type your message..."
          class="flex-1"
          @keyup.enter="sendMessage"
        />
        <UiButton
          :disabled="sending || !newMessage.trim()"
          @click="sendMessage"
        >
          Send
        </UiButton>
      </div>

      <div class="mt-4 text-sm text-muted-foreground">
        Model: {{ model }} | Temperature: {{ temperature }}
      </div>
    </div>
  </div>
</template>

