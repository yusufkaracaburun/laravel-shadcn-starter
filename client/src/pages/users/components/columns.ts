import type { ColumnDef } from '@tanstack/vue-table'

import { h } from 'vue'

import type { User } from '@/services/users.service'

import DataTableColumnHeader from '@/components/data-table/column-header.vue'
import { SelectColumn } from '@/components/data-table/table-columns'
import { Copy } from '@/components/sva-ui/copy'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import Badge from '@/components/ui/badge/Badge.vue'

import DataTableRowActions from './data-table-row-actions.vue'

export const columns: ColumnDef<User>[] = [
  SelectColumn as ColumnDef<User>,
  {
    accessorKey: 'name',
    header: ({ column }) => h(DataTableColumnHeader<User>, { column, title: 'Name' }),
    cell: ({ row }) => {
      const user = row.original
      const nameValue = row.getValue('name')
      const name = (typeof nameValue === 'string' ? nameValue : '—') || '—'
      const profilePhotoUrl = user.profile_photo_url

      // Get initials from name
      const getInitials = (name: string): string => {
        if (!name || name === '—')
          return '?'
        const parts = name.trim().split(/\s+/)
        if (parts.length >= 2) {
          return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
        }
        return name[0].toUpperCase()
      }

      return h('div', { class: 'flex items-center gap-3' }, [
        h(Avatar, { class: 'size-8' }, {
          default: () => [
            profilePhotoUrl
              ? h(AvatarImage, { src: profilePhotoUrl, alt: name })
              : null,
            h(AvatarFallback, {}, () => getInitials(name)),
          ],
        }),
        h('span', { class: 'font-medium' }, name),
      ])
    },
    enableSorting: true,
    enableHiding: false,
    enableResizing: true,
  },

  {
    accessorKey: 'email',
    header: ({ column }) => h(DataTableColumnHeader<User>, { column, title: 'Email' }),
    cell: ({ row }) => {
      const email = row.getValue('email')
      const emailStr = email && typeof email === 'string' ? email : ''
      return h('div', {}, [
        h('span', {}, emailStr || '—'),
        h(Copy, { class: 'ml-2', size: 'sm', content: emailStr }),
      ])
    },
    enableSorting: true,
    enableResizing: true,
  },

  {
    accessorKey: 'email_verified_at',
    header: ({ column }) => h(DataTableColumnHeader<User>, { column, title: 'Verified' }),
    cell: ({ row }) => {
      const verifiedAt = row.getValue('email_verified_at')
      return h(
        Badge,
        { variant: verifiedAt ? 'default' : 'secondary' },
        () => verifiedAt ? 'Verified' : 'Unverified',
      )
    },
    enableSorting: false,
    enableResizing: true,
  },

  {
    accessorKey: 'created_at',
    header: ({ column }) => h(DataTableColumnHeader<User>, { column, title: 'Created At' }),
    cell: ({ row }) => {
      const createdAt = row.getValue('created_at')
      if (!createdAt || typeof createdAt !== 'string') {
        return h('div', {}, '—')
      }
      const date = new Date(createdAt)
      return h('div', {}, date.toLocaleDateString())
    },
    enableSorting: true,
    enableResizing: true,
  },

  {
    id: 'actions',
    cell: ({ row }) => h(DataTableRowActions, { row }),
  },
]
