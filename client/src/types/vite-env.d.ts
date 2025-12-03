/// <reference types="vite/client" />

/**
 * CookieStore API type definitions
 * Reference: https://developer.mozilla.org/en-US/docs/Web/API/CookieStore
 */
interface CookieStore extends EventTarget {
  get: (name: string) => Promise<CookieListItem | null>
  getAll: (name?: string) => Promise<CookieList>
  set: ((name: string, value: string) => Promise<void>) & ((options: CookieStoreSetOptions) => Promise<void>)
  delete: ((name: string) => Promise<void>) & ((options: CookieStoreDeleteOptions) => Promise<void>)
  set: (options: CookieStoreSetOptions) => Promise<void>
  delete: (options: CookieStoreDeleteOptions) => Promise<void>
}

interface CookieListItem {
  name: string
  value: string
  domain: string | null
  path: string
  expires: number | null
  secure: boolean
  sameSite: 'strict' | 'lax' | 'none'
}

type CookieList = CookieListItem[]

interface CookieStoreSetOptions {
  name: string
  value: string
  expires?: number | Date
  domain?: string
  path?: string
  secure?: boolean
  sameSite?: 'strict' | 'lax' | 'none'
  partitioned?: boolean
}

interface CookieStoreDeleteOptions {
  name: string
  domain?: string
  path?: string
}

declare const cookieStore: CookieStore
// vite-env.d.ts
/// <reference types="vite-plugin-pages/client" />
/// <reference types="unplugin-vue-router/client" />
