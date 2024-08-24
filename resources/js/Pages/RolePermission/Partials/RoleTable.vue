<template>
  <div class="w-full overflow-hidden rounded-lg shadow-xs">
    <div class="w-full overflow-x-auto">
      <table class="w-full whitespace-no-wrap">
        <thead>
          <tr class="text-xs font-semibold tracking-wide text-left uppercase border-b">
            <th class="px-4 py-3">Role</th>
            <th class="px-4 py-3">Guard</th>
            <th class="px-4 py-3">Created at</th>
            <th class="px-4 py-3">Updated at</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="role in roles" :key="role.id">
            <td class="px-4 py-3 text-sm">{{ role.name }}</td>
            <td class="px-4 py-3 text-sm">{{ role.guard_name }}</td>
            <td class="px-4 py-3 text-sm">
              {{ dayjs(role.created_at).format('HH:mm A, YYYY-MM-DD') }}
            </td>
            <td class="px-4 py-3 text-sm">
              {{ dayjs(role.updated_at).format('HH:mm A, YYYY-MM-DD') }}
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center space-x-4">
                <SecondaryButton aria-label="Edit" @click="edit(role)">
                  <PencilIcon class="w-5 h-5" />
                </SecondaryButton>
                <SecondaryButton aria-label="Delete" @click="remove(role)">
                  <TrashIcon class="w-5 h-5" />
                </SecondaryButton>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'
  import SecondaryButton from '@/Components/SecondaryButton.vue'
  import { Role } from './../interfaces/Role'
  import { PropType } from 'vue'
  import dayjs from 'dayjs'

  // eslint-disable-next-line no-unused-vars
  const props = defineProps({
    roles: {
      type: Array as PropType<Role[]>,
      required: true,
    },
  })

  const emit = defineEmits(['edit', 'remove'])

  const edit = (role: Role) => {
    emit('edit', role)
  }

  const remove = (role: Role) => {
    emit('remove', role)
  }
</script>
