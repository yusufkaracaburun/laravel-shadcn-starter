import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'

import FormSection from '@/components/form-section.vue'

describe('FormSection', () => {
  describe('when rendering', () => {
    it('should render with title and description', () => {
      // Arrange
      const wrapper = mount(FormSection, {
        slots: {
          title: 'Test Title',
          description: 'Test Description',
        },
      })

      // Assert
      expect(wrapper.text()).toContain('Test Title')
      expect(wrapper.text()).toContain('Test Description')
    })

    it('should render form content', () => {
      // Arrange
      const wrapper = mount(FormSection, {
        slots: {
          title: 'Test Title',
          form: '<input type="text" />',
        },
      })

      // Assert
      expect(wrapper.find('input[type="text"]').exists()).toBe(true)
    })

    it('should render actions when provided', () => {
      // Arrange
      const wrapper = mount(FormSection, {
        slots: {
          title: 'Test Title',
          actions: '<button>Submit</button>',
        },
      })

      // Assert
      expect(wrapper.find('button').exists()).toBe(true)
      expect(wrapper.text()).toContain('Submit')
    })

    it('should emit submitted event on form submit', async () => {
      // Arrange
      const wrapper = mount(FormSection, {
        slots: {
          title: 'Test Title',
        },
      })

      // Act
      const form = wrapper.find('form')
      await form.trigger('submit.prevent')

      // Assert
      expect(wrapper.emitted('submitted')).toBeTruthy()
      expect(wrapper.emitted('submitted')).toHaveLength(1)
    })
  })
})

