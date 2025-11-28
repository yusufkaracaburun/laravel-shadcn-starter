import { describe, expect, it } from 'vitest'
import { useSidebar } from '@/composables/use-sidebar'

describe('useSidebar', () => {
  it('should return navData', () => {
    const { navData } = useSidebar()
    expect(navData.value).toBeDefined()
    expect(Array.isArray(navData.value)).toBe(true)
  })

  it('should have navData with groups', () => {
    const { navData } = useSidebar()
    expect(navData.value?.length).toBeGreaterThan(0)
    expect(navData.value?.[0]).toHaveProperty('title')
    expect(navData.value?.[0]).toHaveProperty('items')
  })

  it('should have General group with Dashboard', () => {
    const { navData } = useSidebar()
    const generalGroup = navData.value?.find(group => group.title === 'General')
    expect(generalGroup).toBeDefined()
    const dashboard = generalGroup?.items?.find(item => item.title === 'Dashboard')
    expect(dashboard).toBeDefined()
    expect(dashboard?.url).toBe('/dashboard')
  })
})

