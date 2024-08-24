<template>
  <div class="w-full mb-8 overflow-hidden rounded-lg shadow-xs">
    <div class="w-full overflow-x-auto">
      <table class="w-full whitespace-no-wrap">
        <thead>
          <tr class="font-semibold tracking-wide text-left uppercase border-b bg-base-200">
            <th class="px-4 py-3">Permissions | Roles</th>
            <th v-for="role in roles" :key="role.id" class="px-4 py-3">
              <div class="flex items-center justify-center gap-3">
                <span class="">{{ role.name }}</span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="permission in permissions" :key="permission.id">
            <td class="px-4 py-3 font-semibold">
              {{ permission.name }}
            </td>
            <td v-for="role in roles" :key="role.id" class="px-4 py-3">
              <label class="flex justify-center dark:text-gray-400">
                <input
                  :checked="hasPermission(role, permission)"
                  type="checkbox"
                  :name="role.name"
                  class="checkbox checkbox-primary"
                  @change="updatePermission(role, permission)"
                />
              </label>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { Permission } from '../interfaces/Permission'
  import { Role } from '../interfaces/Role'
  import { PropType } from 'vue'
  import _ from 'underscore'

  defineProps({
    permissions: {
      type: Array as PropType<Permission[]>,
      required: true,
    },
    roles: {
      type: Array as PropType<Role[]>,
      required: true,
    },
  })

  const emit = defineEmits(['add', 'remove'])

  const hasPermission = (role: Role, permission: Permission) => {
    return _.some(role.permissions, { id: permission.id })
  }

  const add = (role: Role, permission: Permission) => {
    emit('add', {
      role,
      permission,
    })
  }

  const remove = (role: Role, permission: Permission) => {
    emit('remove', {
      role,
      permission,
    })
  }

  const updatePermission = (role: Role, permission: Permission) => {
    if (hasPermission(role, permission)) {
      remove(role, permission)
    } else {
      add(role, permission)
    }
  }
</script>
