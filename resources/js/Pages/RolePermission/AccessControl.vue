<template>
    <Head title="Access Control" />

    <DefaultLayout title="Access Control">
        <AccessControlTable
            :roles="roles"
            :permissions="permissions"
            @add="add"
            @remove="remove"
        />
    </DefaultLayout>
</template>

<script setup lang="ts">
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import AccessControlTable from './Partials/AccessControlTable.vue';
import { Permission } from './interfaces/Permission'
import { Role } from './interfaces/Role'
import { PropType } from 'vue';
import { Head, router, usePage } from "@inertiajs/vue3";
import { useToast } from "vue-toastification";

type RolePermissionPair = {
  role: Role;
  permission: Permission;
};

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

let url = route("role.add-permission", { role: 1 });
console.log({url})

const toast = useToast();

const remove = ({role, permission}: RolePermissionPair) => {
    try {
        let url = route("role.remove-permission", { role: role.id });
        router.delete(url, {
            data: { permission: permission.name },
            onSuccess: () => {
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

const add = ({role, permission}: RolePermissionPair) => {
    try {
        let url = route("role.add-permission", { role: role.id });
        router.post(url,{ role: role.id, permission: permission.name }, {
            onSuccess: () => {
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
</script>
