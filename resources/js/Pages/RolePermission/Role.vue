<template>
    <Head title="Role Management" />

    <DefaultLayout title="Role Management">
        <div class="flex justify-end">
            <SecondaryButton @click="openAddModal" type="button">Add Role</SecondaryButton>
        </div>

        <RoleTable :roles="roles" @remove="remove" @edit="editRole" />

        <Modal :show="showAddModal" @close="closeAddModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Create a new role
                </h2>

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
                <h2 class="text-lg font-medium text-gray-900">
                    {{ targetRole?.name }}
                </h2>

                <div class="mt-6">
                    <InputLabel for="name" value="Name of the role" />

                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-3/4"
                        placeholder="A unique role name"
                        @keyup.enter="update"
                    />

                    <InputError :message="form.errors.name" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <DangerButton @click="closeEditModal"> Cancel </DangerButton>
                    <PrimaryButton @click="update"> Update </PrimaryButton>
                </div>
            </div>
        </Modal>
    </DefaultLayout>
</template>

<script setup lang="ts">
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import RoleTable from './Partials/RoleTable.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import Modal from '@/Components/Modal.vue'
import { Role } from './interfaces/Role'
import { PropType, ref } from "vue";
import { useToast } from "vue-toastification";
import { router, usePage, useForm, Head } from "@inertiajs/vue3";

defineProps({
    roles: {
        type: Array as PropType<Role[]>,
        required: true,
    },
})

const toast = useToast();

const form = useForm({
    name: '',
});
const showEditModal = ref(false);
const showAddModal = ref(false);
const targetRole = ref<Role|null>(null)

const remove = (role: Role) => {
    try {
        let url = route("role.destroy", { role: role.id });
        router.delete(url, {
            onSuccess: (response) => {
                if(usePage().props.error) {
                    toast.error(usePage().props.error)
                } else {
                    toast.success(usePage().props.success);
                }
            },
        });
    } catch (error) {
        console.error(error);
    }
}

const update = () => {
    let url = route("role.update", { role: targetRole.value?.id });

    form.patch(url, {
        preserveScroll: true,
        onSuccess: () => {
            if(usePage().props.error) {
                toast.error(usePage().props.error)
            } else {
                toast.success(usePage().props.success);
                closeEditModal()
            }
        },
        onError: () => {
            toast.error('Request failed');
        },
    });
}

const add = () => {
    let url = route("role.store");

    form.post(url, {
        preserveScroll: true,
        onSuccess: () => {
            if(usePage().props.error) {
                toast.error(usePage().props.error)
            } else {
                toast.success(usePage().props.success);
            }

            closeAddModal()
        },
        onError: () => {
            toast.error('Request failed');
        },
    });
}

const editRole = (role: Role) => {
    targetRole.value = role
    form.name = role.name
    showEditModal.value = true
}

const closeEditModal = () => {
    form.reset()
    showEditModal.value = false
    targetRole.value = null
}

const openAddModal = () => {
    targetRole.value = null
    form.name = ''
    showAddModal.value = true
}

const closeAddModal = () => {
    showAddModal.value = false
    form.name = ''
}
</script>
