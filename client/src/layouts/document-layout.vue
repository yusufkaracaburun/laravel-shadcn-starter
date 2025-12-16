<script setup lang="ts">
// Props can be added here if the layout needs configuration
</script>

<template>
  <div class="min-h-screen">
    <slot name="navbar"></slot>

    <!-- Main Content Area: Loading, Error, or Document Content -->
    <slot name="main-content"></slot>
  </div>
</template>

<style scoped>
/* Add any common styles for the document layout here */
</style>

