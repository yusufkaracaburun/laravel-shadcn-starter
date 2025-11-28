import { mount } from '@vue/test-utils'
import { describe, expect, it } from 'vitest'

import Loading from '@/components/loading.vue'

describe('loading', () => {
  it('should render loading component', () => {
    const wrapper = mount(Loading)
    expect(wrapper.exists()).toBe(true)
  })

  it('should render successfully', () => {
    const wrapper = mount(Loading)
    expect(wrapper.html()).toBeTruthy()
  })
})
