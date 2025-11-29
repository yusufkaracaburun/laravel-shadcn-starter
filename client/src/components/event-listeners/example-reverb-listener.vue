<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'

import { useEcho } from '@/composables/use-echo'

const echo = useEcho()
const messages = ref<Array<{ message: string, timestamp: string }>>([])

onMounted(() => {
  if (!echo) {
    console.warn('Echo is not available. Make sure Reverb is configured and running.')
    return
  }

  // Listen to a public channel
  const channel = echo.channel('example-channel')
  channel.listen('.example.event', (data: { message: string, timestamp: string }) => {
    messages.value.push(data)
    console.warn('Received message:', data)
  })
  console.warn('Listening to example-channel for example.event', channel)
})

onUnmounted(() => {
  if (echo) {
    echo.leave('example-channel')
  }
})
</script>

<template>
  <div class="p-4 border rounded-lg">
    <h3 class="text-lg font-semibold mb-2">
      Reverb Example Listener
    </h3>
    <p v-if="!echo" class="text-sm text-muted-foreground">
      Echo is not available. Make sure Reverb is configured and running.
    </p>
    <div v-else>
      <p class="text-sm text-muted-foreground mb-4">
        Listening to <code>example-channel</code> for <code>example.event</code>
      </p>
      <div v-if="messages.length === 0" class="text-sm text-muted-foreground">
        No messages received yet. Broadcast an event from your Laravel application to see it here.
      </div>
      <div v-else class="space-y-2">
        <div v-for="(msg, index) in messages" :key="index" class="p-2 bg-muted rounded text-sm">
          <div class="font-medium">
            {{ msg.message }}
          </div>
          <div class="text-xs text-muted-foreground">
            {{ msg.timestamp }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
