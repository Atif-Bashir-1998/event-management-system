<template>
  <Head title="User Management" />

  <DefaultLayout title="User Management">
    <div class="flex justify-end">
      <SecondaryButton @click="openAddModal" type="button">Create user</SecondaryButton>
    </div>

    <UserTable :users="users" @remove="remove" @edit="editUser" @details="details" />

    <Modal :show="showAddModal" @close="closeAddModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">Create a new role</h2>

        <div class="mt-6">
          <InputLabel for="name" value="Name of the role" />

          <TextInput
            id="name"
            v-model="form.name"
            type="text"
            class="mt-1 block w-3/4"
            placeholder="A unique role name"
            @keyup.enter="add"
          />

          <InputError :message="form.errors.name" class="mt-2" />
        </div>

        <div class="mt-6 flex justify-end gap-2">
          <DangerButton @click="closeAddModal"> Cancel </DangerButton>
          <PrimaryButton @click="add"> Add </PrimaryButton>
        </div>
      </div>
    </Modal>
    <Modal :show="showEditModal" @close="closeEditModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-primary">
          {{ targetUser?.name }}
        </h2>

        <div class="mt-6 grid grid-cols-2 gap-4">
          <div>
            <InputLabel for="name" value="Name" />

            <TextInput
              id="name"
              v-model="form.name"
              type="text"
              class="mt-1 block w-full"
              placeholder="Name of the user"
            />

            <InputError :message="form.errors.name" class="mt-2" />
          </div>
          <div>
            <InputLabel for="email" value="Email" />

            <TextInput
              id="email"
              v-model="form.email"
              type="email"
              class="mt-1 block w-full"
              placeholder="Email"
            />

            <InputError :message="form.errors.name" class="mt-2" />
          </div>
          <div>
            <InputLabel for="password" value="Password" />

            <TextInput
              id="password"
              v-model="form.password"
              type="text"
              class="mt-1 block w-full"
              placeholder="Password"
            />

            <InputError :message="form.errors.name" class="mt-2" />
          </div>
          <div class="block mt-4">
            <label class="flex items-end h-full">
              <Checkbox name="remember" v-model:checked="form.is_email_verified" />
              <span class="ms-2 text-sm text-gray-600">Is email verified?</span>
            </label>
          </div>
          <div>
            <InputLabel for="roles" value="Roles" />

            <v-select
              multiple
              v-model="form.roles"
              :options="roles"
              label="name"
              appendToBody
            ></v-select>

            <InputError :message="form.errors.roles" class="mt-2" />
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
          <DangerButton @click="closeEditModal"> Cancel </DangerButton>
          <PrimaryButton @click="update"> Update </PrimaryButton>
        </div>
      </div>
    </Modal>
    <Modal :show="showPermissionsModal" @close="closePermissionModal">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">
          {{ targetUser?.name }} - {{ targetUser?.email }}
        </h2>

        <div class="mt-6 space-y-4">
          <div class="space-y-2 border-2 border-dashed border-warning p-2">
            <p class="font-bold">Assigned roles</p>
            <div class="flex gap-2">
              <span v-for="role in targetUser?.roles" :key="role.id" class="badge badge-info">{{
                role.name
              }}</span>
            </div>
          </div>

          <div class="space-y-2 border-2 border-dashed border-warning p-2">
            <p class="font-bold">Special permissions</p>
            <div v-if="targetUser?.permissions.length" class="flex flex-wrap gap-2">
              <span
                v-for="permission in targetUser?.permissions"
                :key="permission.id"
                @click="removePermission(permission)"
                class="group badge hover:badge-error gap-2 cursor-pointer"
              >
                <TrashIcon class="w-3 h-3 hidden group-hover:block" />
                {{ permission.name }}
              </span>
            </div>
            <div v-else role="alert">
              <span>User has no special permissions</span>
            </div>
          </div>

          <div
            v-if="targetUser?.unassigned_permissions.length"
            class="space-y-2 border-2 border-dashed p-2"
          >
            <p class="font-bold">Available permissions for this user</p>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="permission in targetUser?.unassigned_permissions"
                :key="permission.id"
                @click="addPermission(permission)"
                class="group badge hover:badge-success cursor-pointer gap-2"
              >
                <CheckIcon class="w-3 h-3 hidden group-hover:block" />
                {{ permission.name }}
              </span>
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
          <DangerButton @click="closePermissionModal"> Close </DangerButton>
        </div>
      </div>
    </Modal>
  </DefaultLayout>
</template>

<script setup lang="ts">
  import DefaultLayout from '@/Layouts/DefaultLayout.vue'
  import UserTable from './Partials/UserTable.vue'
  import InputLabel from '@/Components/InputLabel.vue'
  import TextInput from '@/Components/TextInput.vue'
  import InputError from '@/Components/InputError.vue'
  import PrimaryButton from '@/Components/PrimaryButton.vue'
  import SecondaryButton from '@/Components/SecondaryButton.vue'
  import DangerButton from '@/Components/DangerButton.vue'
  import Checkbox from '@/Components/Checkbox.vue'
  import Modal from '@/Components/Modal.vue'
  import { CheckIcon, TrashIcon } from '@heroicons/vue/24/outline'
  import { PropType, ref } from 'vue'
  import { useToast } from 'vue-toastification'
  import { router, usePage, useForm, Head } from '@inertiajs/vue3'
  import { User } from './interfaces/User'
  import { Role } from '@/Pages/RolePermission/interfaces/Role'
  import { Permission } from '@/Pages/RolePermission/interfaces/Permission'
  // @ts-ignore
  import vSelect from 'vue-select'
  import { computed } from 'vue'

  type FormInput = {
    name: string
    email: string
    is_email_verified: boolean
    roles: Role[]
    password: string
  }

  const props = defineProps({
    users: {
      type: Array as PropType<User[]>,
      required: true,
    },
    roles: {
      type: Array as PropType<Role[]>,
      required: true,
    },
    permissions: {
      type: Array as PropType<Permission[]>,
      required: true,
    },
  })

  const toast = useToast()

  const form = useForm<FormInput>({
    name: '',
    email: '',
    is_email_verified: false,
    roles: [],
    password: '',
  })
  const showEditModal = ref(false)
  const showAddModal = ref(false)
  const showPermissionsModal = ref(false)
  const targetUser = ref<User | null>(null)

  const remove = (user: User) => {
    try {
      let url = route('user.destroy', { user: user.id })
      router.delete(url, {
        onSuccess: (response) => {
          if (usePage().props.error) {
            toast.error(usePage().props.error)
          } else {
            toast.success(usePage().props.success)
          }
        },
      })
    } catch (error) {
      console.error(error)
    }
  }

  const update = () => {
    let url = route('user.update', { user: targetUser.value?.id })

    form
      .transform((data) => {
        return {
          ...data,
          roles: data.roles.map((role) => role.name),
        }
      })
      .patch(url, {
        preserveScroll: true,
        onSuccess: () => {
          if (usePage().props.error) {
            toast.error(usePage().props.error)
          } else {
            toast.success(usePage().props.success)
            closeEditModal()
          }
        },
        onError: () => {
          toast.error('Request failed')
        },
      })
  }

  const add = () => {
    let url = route('user.store')

    form.post(url, {
      preserveScroll: true,
      onSuccess: () => {
        if (usePage().props.error) {
          toast.error(usePage().props.error)
        } else {
          toast.success(usePage().props.success)
        }

        closeAddModal()
      },
      onError: () => {
        toast.error('Request failed')
      },
    })
  }

  const addPermission = (permission: Permission) => {
    try {
      let url = route('user.add-permission', { user: targetUser.value?.id })
      router.post(
        url,
        {
          permission: permission.name,
        },
        {
          preserveState: true,
          onSuccess: (response) => {
            if (usePage().props.error) {
              toast.error(usePage().props.error)
            } else {
              toast.success(usePage().props.success)

              targetUser.value =
                props.users.find((user) => user.id === targetUser.value?.id) ?? null
            }
          },
        },
      )
    } catch (error) {
      console.error(error)
    }
  }

  const removePermission = (permission: Permission) => {
    try {
      let url = route('user.remove-permission', {
        user: targetUser.value?.id,
      })
      router.delete(url, {
        data: {
          permission: permission.name,
        },
        preserveState: true,
        onSuccess: (response) => {
          if (usePage().props.error) {
            toast.error(usePage().props.error)
          } else {
            toast.success(usePage().props.success)

            targetUser.value = props.users.find((user) => user.id === targetUser.value?.id) ?? null
          }
        },
      })
    } catch (error) {
      console.error(error)
    }
  }

  const details = (user: User) => {
    targetUser.value = user
    showPermissionsModal.value = true
  }

  const editUser = (user: User) => {
    targetUser.value = user
    form.name = user.name
    form.email = user.email
    form.is_email_verified = !!user.email_verified_at
    form.roles = user.roles

    showEditModal.value = true
  }

  const closeEditModal = () => {
    form.reset()
    showEditModal.value = false
    targetUser.value = null
  }

  const openAddModal = () => {
    targetUser.value = null
    form.reset()
    showAddModal.value = true
  }

  const closeAddModal = () => {
    showAddModal.value = false
    form.reset()
  }

  const closePermissionModal = () => {
    showPermissionsModal.value = false
    targetUser.value = null
  }
</script>
