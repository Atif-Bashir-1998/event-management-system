<template>
    <Head title="Permission Management" />

    <DefaultLayout title="Permission Management">
        <div class="flex justify-end">
            <SecondaryButton @click="openAddModal" type="button">Add Permission</SecondaryButton>
        </div>

        <PermissionTable :permissions="permissions" @remove="remove" @edit="editPermission" />

        <Modal :show="showAddModal" @close="closeAddModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Create a new permission
                </h2>

                <div class="mt-6">
                    <InputLabel for="name" value="Name of the permission" />

                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-3/4"
                        placeholder="A unique permission name"
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
                    {{ targetPermission?.name }}
                </h2>

                <div class="mt-6">
                    <InputLabel for="name" value="Name of the permission" />

                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-3/4"
                        placeholder="A unique permission name"
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
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import Modal from '@/Components/Modal.vue'
import { Permission } from './interfaces/Permission'
import { PropType, ref } from "vue";
import { useToast } from "vue-toastification";
import { router, usePage, useForm, Head } from "@inertiajs/vue3";
import PermissionTable from './Partials/PermissionTable.vue'

defineProps({
    permissions: {
        type: Array as PropType<Permission[]>,
        required: true,
    },
})

const toast = useToast();

const form = useForm({
    name: '',
});
const showEditModal = ref(false);
const showAddModal = ref(false);
const targetPermission = ref<Permission|null>(null)

const remove = (permission: Permission) => {
    try {
        let url = route("permission.destroy", { permission: permission.id });
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
    let url = route("permission.update", { permission: targetPermission.value?.id });

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
    let url = route("permission.store");

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

const editPermission = (permission: Permission) => {
    targetPermission.value = permission
    form.name = permission.name
    showEditModal.value = true
}

const closeEditModal = () => {
    form.reset()
    showEditModal.value = false
    targetPermission.value = null
}

const openAddModal = () => {
    targetPermission.value = null
    form.name = ''
    showAddModal.value = true
}

const closeAddModal = () => {
    showAddModal.value = false
    form.name = ''
}
</script>
