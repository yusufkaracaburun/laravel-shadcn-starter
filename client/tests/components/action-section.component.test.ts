import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'

import ActionSection from '@/components/action-section.vue'

describe('ActionSection', () => {
  describe('when rendering', () => {
    it('should render with title and description', () => {
      // Arrange
      const wrapper = mount(ActionSection, {
        slots: {
          title: 'Test Title',
          description: 'Test Description',
        },
      })

      // Assert
      expect(wrapper.text()).toContain('Test Title')
      expect(wrapper.text()).toContain('Test Description')
    })

    it('should render content', () => {
      // Arrange
      const wrapper = mount(ActionSection, {
        slots: {
          title: 'Test Title',
          content: '<p>Test Content</p>',
        },
      })

      // Assert
      expect(wrapper.find('p').exists()).toBe(true)
      expect(wrapper.text()).toContain('Test Content')
    })

    it('should render aside slot when provided', () => {
      // Arrange
      const wrapper = mount(ActionSection, {
        slots: {
          title: 'Test Title',
          aside: '<span>Aside Content</span>',
        },
      })

      // Assert
      expect(wrapper.find('span').exists()).toBe(true)
      expect(wrapper.text()).toContain('Aside Content')
    })
  })
})

