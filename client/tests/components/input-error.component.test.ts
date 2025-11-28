import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'

import InputError from '@/components/input-error.vue'

describe('InputError', () => {
  describe('when rendering', () => {
    it('should not render when message is null', () => {
      // Arrange
      const wrapper = mount(InputError, {
        props: {
          message: null,
        },
      })

      // Assert
      expect(wrapper.find('p').exists()).toBe(false)
    })

    it('should not render when message is empty string', () => {
      // Arrange
      const wrapper = mount(InputError, {
        props: {
          message: '',
        },
      })

      // Assert
      expect(wrapper.find('p').exists()).toBe(false)
    })

    it('should render error message when provided', () => {
      // Arrange
      const errorMessage = 'This field is required'

      // Act
      const wrapper = mount(InputError, {
        props: {
          message: errorMessage,
        },
      })

      // Assert
      expect(wrapper.find('p').exists()).toBe(true)
      expect(wrapper.text()).toContain(errorMessage)
      expect(wrapper.find('p').classes()).toContain('text-destructive')
    })

    it('should render first message when message is an array', () => {
      // Arrange
      const errorMessages = ['First error', 'Second error']

      // Act
      const wrapper = mount(InputError, {
        props: {
          message: errorMessages,
        },
      })

      // Assert
      expect(wrapper.text()).toContain('First error')
      expect(wrapper.text()).not.toContain('Second error')
    })
  })
})

