<template>
  <div class="w-full overflow-hidden rounded-lg shadow-xs">
    <div class="w-full overflow-x-auto">
      <table class="w-full whitespace-no-wrap">
        <thead>
          <tr class="text-xs font-semibold tracking-wide text-left uppercase border-b">
            <th class="px-4 py-3">Permission</th>
            <th class="px-4 py-3">Guard</th>
            <th class="px-4 py-3">Created at</th>
            <th class="px-4 py-3">Updated at</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="permission in permissions" :key="permission.id">
            <td class="px-4 py-3 text-sm">{{ permission.name }}</td>
            <td class="px-4 py-3 text-sm">
              {{ permission.guard_name }}
            </td>
            <td class="px-4 py-3 text-sm">
              {{ dayjs(permission.created_at).format('HH:mm A, YYYY-MM-DD') }}
            </td>
            <td class="px-4 py-3 text-sm">
              {{ dayjs(permission.updated_at).format('HH:mm A, YYYY-MM-DD') }}
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center space-x-4">
                <SecondaryButton aria-label="Edit" @click="edit(permission)">
                  <PencilIcon class="w-5 h-5" />
                </SecondaryButton>
                <SecondaryButton aria-label="Delete" @click="remove(permission)">
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
  import { Permission } from './../interfaces/Permission'
  import { PropType } from 'vue'
  import dayjs from 'dayjs'

  // eslint-disable-next-line no-unused-vars
  const props = defineProps({
    permissions: {
      type: Array as PropType<Permission[]>,
      required: true,
    },
  })

  const emit = defineEmits(['edit', 'remove'])

  const edit = (permission: Permission) => {
    emit('edit', permission)
  }

  const remove = (permission: Permission) => {
    emit('remove', permission)
  }
</script>
