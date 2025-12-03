import { useRoute, useRouter } from 'vue-router'

export function useRouteHelper() {
  const router = useRouter()
  const route = useRoute()

  function routeName(name: string, params: Record<string, any> = {}) {
    try {
      return router.resolve({ name: name as any, params }).href
    } catch {
      console.warn(`Route "${name}" not found`)
      return '#'
    }
  }

  function routeCurrent(name: string) {
    return route.name === name
  }

  function routeVisit(name: string, params: Record<string, any> = {}) {
    router.push({ name: name as any, params })
  }

  return {
    route: routeName,
    routeCurrent,
    routeVisit,
    router,
    routeName,
  }
}
