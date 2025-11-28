import { describe, expect, it } from 'vitest'
import { ref } from 'vue'
import { cn, valueUpdater } from '@/lib/utils'

describe('utils', () => {
  describe('cn', () => {
    it('should merge class names', () => {
      expect(cn('foo', 'bar')).toBe('foo bar')
    })

    it('should handle conditional classes', () => {
      expect(cn('foo', false && 'bar', 'baz')).toBe('foo baz')
    })

    it('should merge tailwind classes correctly', () => {
      expect(cn('px-2 py-1', 'px-4')).toBe('py-1 px-4')
    })

    it('should handle empty inputs', () => {
      expect(cn()).toBe('')
      expect(cn('', null, undefined)).toBe('')
    })

    it('should handle arrays', () => {
      expect(cn(['foo', 'bar'], 'baz')).toBe('foo bar baz')
    })

    it('should handle objects', () => {
      expect(cn({ foo: true, bar: false, baz: true })).toBe('foo baz')
    })
  })

  describe('valueUpdater', () => {
    it('should update ref with direct value', () => {
      const refValue = ref(10)
      valueUpdater(20, refValue)
      expect(refValue.value).toBe(20)
    })

    it('should update ref with function', () => {
      const refValue = ref(10)
      valueUpdater((val) => val * 2, refValue)
      expect(refValue.value).toBe(20)
    })

    it('should handle string values', () => {
      const refValue = ref('hello')
      valueUpdater('world', refValue)
      expect(refValue.value).toBe('world')
    })

    it('should handle function with string', () => {
      const refValue = ref('hello')
      valueUpdater((val) => `${val} world`, refValue)
      expect(refValue.value).toBe('hello world')
    })
  })
})

