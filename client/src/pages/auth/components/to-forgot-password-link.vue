<script lang="ts" setup></script>

<template>
  <UiButton
    variant="link"
    class="text-muted-foreground"
    data-testid="login-form_forgot-password_link"
    @click="$router.push('/auth/forgot-password')"
  >
    Forgot password?
  </UiButton>
</template>
