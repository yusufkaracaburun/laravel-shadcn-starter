import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import { Button } from '@/components/ui/button'

describe('Button', () => {
  it('should render button', () => {
    const wrapper = mount(Button, {
      slots: {
        default: 'Click me',
      },
    })
    expect(wrapper.exists()).toBe(true)
    expect(wrapper.text()).toBe('Click me')
  })

  it('should emit click event', async () => {
    const wrapper = mount(Button, {
      slots: {
        default: 'Click me',
      },
    })
    await wrapper.trigger('click')
    expect(wrapper.emitted('click')).toBeTruthy()
  })

  it('should apply variant classes', () => {
    const wrapper = mount(Button, {
      props: {
        variant: 'destructive',
      },
      slots: {
        default: 'Delete',
      },
    })
    const button = wrapper.find('button')
    expect(button.classes().some(cls => cls.includes('destructive') || cls.includes('bg-destructive'))).toBe(true)
  })

  it('should apply size classes', () => {
    const wrapper = mount(Button, {
      props: {
        size: 'lg',
      },
      slots: {
        default: 'Large Button',
      },
    })
    const button = wrapper.find('button')
    expect(button.classes().some(cls => cls.includes('lg') || cls.includes('h-10'))).toBe(true)
  })

  it('should be disabled when disabled prop is true', () => {
    const wrapper = mount(Button, {
      props: {
        disabled: true,
      },
      slots: {
        default: 'Disabled',
      },
    })
    const button = wrapper.find('button')
    expect(button.attributes('disabled')).toBeDefined()
  })

  it('should not emit click when disabled', async () => {
    const wrapper = mount(Button, {
      props: {
        disabled: true,
      },
      slots: {
        default: 'Disabled',
      },
    })
    await wrapper.trigger('click')
    expect(wrapper.emitted('click')).toBeFalsy()
  })
})

