<script lang="ts" setup>
import UserForm from './user-form.vue'

defineEmits(['close'])

const title = computed(() => 'Create New User')
const description = computed(() => 'Add a new user to the system. Fill in the required information below.')
</script>

<template>
  <UiDrawerHeader>
    <UiDrawerTitle>
      {{ title }}
    </UiDrawerTitle>
    <UiDrawerDescription>
      {{ description }}
    </UiDrawerDescription>
  </UiDrawerHeader>

  <UserForm class="mt-4" @close="$emit('close')" />
</template>
