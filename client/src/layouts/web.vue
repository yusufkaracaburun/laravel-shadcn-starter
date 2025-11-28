<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { useColorMode } from '@vueuse/core'
import { storeToRefs } from 'pinia'
import { ref } from 'vue'

import { useAuthStore } from '@/stores/auth'
import SignInButton from '@/components/sign-in-button.vue'
import SignUpButton from '@/components/sign-up-button.vue'

const mode = useColorMode({
  attribute: 'class',
  modes: {
    light: '',
    dark: 'dark',
  },
})

const authStore = useAuthStore()
const { isLogin } = storeToRefs(authStore)

const navLinks = [
  { label: 'Features', href: '/#features', external: false },
  { label: 'Pricing', href: '/#pricing', external: false },
]

const githubUrl = 'https://github.com/shipfastlabs/larasonic-vue'
const twitterUrl = 'https://x.com/pushpak1300?ref=larasonic'

const isMenuOpen = ref(false)

function toggleMenu() {
  isMenuOpen.value = !isMenuOpen.value
}

function handleHashLink(e: Event, href: string) {
  const hash = href.split('#')[1]
  if (hash) {
    e.preventDefault()
    const element = document.getElementById(hash)
    if (element) {
      const headerOffset = 80
      const elementPosition = element.getBoundingClientRect().top
      const offsetPosition = elementPosition + window.pageYOffset - headerOffset

      window.scrollTo({
        top: offsetPosition,
        behavior: 'smooth',
      })
    }
  }
  // Close mobile menu if open
  if (isMenuOpen.value) {
    isMenuOpen.value = false
  }
}
</script>

<template>
  <div class="min-h-screen">
    <header
      class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur-sm supports-backdrop-filter:bg-background/60">
      <div class="container mx-auto flex h-16 items-center justify-between">
        <div class="flex items-center">
          <router-link class="flex items-center space-x-2" to="/">
            <Icon icon="lucide:rocket" class="h-6 w-6" aria-hidden="true" />
            <span class="hidden font-bold sm:inline-block">
              Shadcn Vue Admin
            </span>
          </router-link>
          <nav class="hidden md:flex items-center space-x-6 text-sm font-medium sm:ml-4">
            <a v-for="link in navLinks" :key="link.href" :href="link.href"
              class="transition-colors hover:text-foreground/80" :class="[
                link.href.startsWith('http')
                  ? ''
                  : 'text-foreground/60',
              ]" :target="link.href.startsWith('http')
                  ? '_blank'
                  : undefined
                " :rel="link.href.startsWith('http')
                  ? 'noreferrer'
                  : undefined
                " @click="link.href.includes('#') ? handleHashLink($event, link.href) : undefined">
              {{ link.label }}
            </a>
          </nav>
        </div>
        <div class="flex items-center space-x-4">
          <div class="hidden sm:flex space-x-2">
            <template v-if="!isLogin">
              <SignInButton />
              <SignUpButton />
            </template>
            <UiButton v-else variant="outline" as="a" href="/dashboard">
              Dashboard
            </UiButton>
          </div>
          <UiButton variant="ghost" size="icon" aria-label="Toggle Theme"
            @click="mode = mode === 'dark' ? 'light' : 'dark'">
            <Icon class="text-muted-foreground h-6 w-6" :icon="mode === 'dark' ? 'lucide:sun' : 'lucide:moon'
              " />
          </UiButton>
          <a :href="githubUrl" target="_blank" rel="noreferrer" class="hidden sm:inline-block" aria-label="GitHub">
            <Icon icon="mdi:github" class="h-5 w-5" />
          </a>
          <UiButton class="md:hidden" variant="ghost" size="icon" aria-label="Toggle menu" @click="toggleMenu">
            <Icon :icon="isMenuOpen ? 'lucide:x' : 'lucide:menu'" class="h-6 w-6" aria-hidden="true" />
          </UiButton>
        </div>
      </div>
      <!-- Mobile menu -->
      <div v-show="isMenuOpen" class="md:hidden border-t">
        <nav class="flex flex-col p-4 space-y-4">
          <a v-for="link in navLinks" :key="link.href" :href="link.href"
            class="text-sm font-medium transition-colors hover:text-foreground/80" :target="link.href.startsWith('http') ? '_blank' : undefined
              " :rel="link.href.startsWith('http')
                ? 'noreferrer'
                : undefined
              " @click="link.href.includes('#') ? handleHashLink($event, link.href) : toggleMenu">
            {{ link.label }}
          </a>
          <template v-if="!isLogin">
            <SignInButton />
            <SignUpButton />
          </template>
          <UiButton v-else variant="outline" as="a" href="/dashboard" class="w-full" @click="toggleMenu">
            Dashboard
          </UiButton>
          <a :href="githubUrl" target="_blank" rel="noreferrer" class="flex items-center space-x-2 text-sm font-medium"
            @click="toggleMenu">
            <Icon icon="mdi:github" class="h-5 w-5" />
            <span>GitHub</span>
          </a>
        </nav>
      </div>
    </header>

    <main>
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="border-t">
      <div class="container mx-auto px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
          <p class="text-sm flex items-center gap-2 text-center sm:text-left">
            <Icon icon="lucide:rocket" class="size-6" aria-hidden="true" />
            Crafted by
            <a class="underline" :href="twitterUrl" target="_blank" rel="noopener noreferrer">
              Pushpak.
            </a>
            <span>
              Hosted On
              <a class="underline" href="https://sevalla.com/?ref=larasonic" target="_blank" rel="noopener noreferrer">
                Sevalla
              </a>
              ❤️
            </span>
          </p>
          <div class="flex gap-4">
            <Icon class="text-muted-foreground cursor-pointer" :icon="mode === 'dark' ? 'lucide:sun' : 'lucide:moon'
              " @click="mode = mode === 'dark' ? 'light' : 'dark'" />

            <a :href="githubUrl" target="_blank" rel="noreferrer" class="text-muted-foreground hover:text-foreground"
              aria-label="GitHub">
              <Icon icon="mdi:github" class="h-5 w-5" aria-hidden="true" />
            </a>
            <a :href="twitterUrl" target="_blank" rel="noreferrer" class="text-muted-foreground hover:text-foreground"
              aria-label="Twitter">
              <Icon icon="ri:twitter-x-line" class="h-5 w-5" aria-hidden="true" />
            </a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>
