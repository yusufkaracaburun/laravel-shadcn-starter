<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { h } from 'vue'
import { z } from 'zod'

import Button from '@/components/ui/button/Button.vue'
import {
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/components/ui/form'
import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { SendIcon } from '@/composables/use-icons'
import { useToast } from '@/composables/use-toast'

import { userRoleSchema } from '../data/schema'
import { EUserRole } from '../models/users'

const schema = z.object({
  email: z.string().email('Please enter a valid email address.'),
  role: userRoleSchema,
  description: z.string().optional(),
})

const roles = [
  EUserRole.SUPER_ADMIN,
  EUserRole.ADMIN,
  EUserRole.USER,
  EUserRole.CUSTOMER,
  EUserRole.CONTRACTOR,
] as const

const { toast } = useToast()

const userInviteFormSchema = toTypedSchema(schema)
const { handleSubmit } = useForm({
  validationSchema: userInviteFormSchema,
  initialValues: {},
})

const onSubmit = handleSubmit((values) => {
  toast('You submitted the following values:', {
    description: h(
      'pre',
      { class: 'mt-2 w-[340px] rounded-md bg-slate-950 p-4' },
      h('code', { class: 'text-white' }, JSON.stringify(values, null, 2)),
    ),
  })
})
</script>

<template>
  <form class="space-y-8" @submit="onSubmit">
    <FormField v-slot="{ componentField }" name="email">
      <FormItem>
        <FormLabel>Email address</FormLabel>
        <FormControl>
          <Input type="text" v-bind="componentField" />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="role">
      <FormItem>
        <FormLabel>
          Role
          <span class="text-destructive"> *</span>
        </FormLabel>
        <FormControl>
          <Select v-bind="componentField">
            <FormControl>
              <SelectTrigger class="w-full">
                <SelectValue placeholder="Select a role" />
              </SelectTrigger>
            </FormControl>
            <SelectContent>
              <SelectGroup>
                <SelectItem v-for="role in roles" :key="role" :value="role">
                  {{ role }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <FormField v-slot="{ componentField }" name="description">
      <FormItem>
        <FormLabel>Description(Optional)</FormLabel>
        <FormControl>
          <Textarea v-bind="componentField" />
        </FormControl>
        <FormMessage />
      </FormItem>
    </FormField>

    <Button type="submit" class="w-full">
      Invite
      <SendIcon />
    </Button>
  </form>
</template>
