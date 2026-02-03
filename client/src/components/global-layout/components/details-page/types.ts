import type { Component } from 'vue'

export interface SidebarField {
  label: string
  value: string | number | null | undefined
  icon?: Component
  span?: number
}

export interface SidebarStatus {
  label: string
  variant?: 'default' | 'destructive' | 'secondary' | 'outline'
  color?: string
}

export interface RelatedItem {
  id: string | number
  name: string
  avatar?: string
  icon?: Component
  badge?: Component
  onClick?: () => void
}

export interface SidebarTimestamps {
  created?: string
  updated?: string
}

export interface MenuItem {
  label: string
  icon?: Component
  action: string
  variant?: 'default' | 'destructive' | 'secondary' | 'outline'
}

export interface TabField {
  label: string
  value?: string | number | null | undefined
  icon?: Component
  span?: number
}

export interface TabSection {
  title: string
  fields?: TabField[]
}
