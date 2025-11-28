import { useRoute, useRouter } from 'vue-router'

export function useRouteHelper() {
  const router = useRouter()
  const route = useRoute()

  function routeName(name: string, params: Record<string, any> = {}) {
    try {
      return router.resolve({ name, params }).href
    }
    catch (error) {
      console.warn(`Route "${name}" not found`)
      return '#'
    }
  }

  function routeCurrent(name: string) {
    return route.name === name
  }

  function routeVisit(name: string, params: Record<string, any> = {}) {
    router.push({ name, params })
  }

  return {
    route: routeName,
    routeCurrent,
    routeVisit,
    router,
    routeName,
  }
}

