<template>
    <div class="w-full overflow-hidden rounded-lg shadow-xs">
        <div class="w-full overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr
                        class="text-xs font-semibold tracking-wide text-left uppercase border-b"
                    >
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Highest role</th>
                        <th class="px-4 py-3">Created at</th>
                        <th class="px-4 py-3">Updated at</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="user in users"
                        :key="user.id"
                    >
                        <td class="px-4 py-3 text-sm">{{ user.name }}</td>
                        <td class="px-4 py-3 text-sm">{{ user.email }}</td>
                        <td class="px-4 py-3 text-sm flex flex-wrap">
                            <span class="badge badge-info">{{ user.highest_role }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm">{{ dayjs(user.created_at).format('HH:mm A, YYYY-MM-DD') }}</td>
                        <td class="px-4 py-3 text-sm">{{ dayjs(user.updated_at).format('HH:mm A, YYYY-MM-DD') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center space-x-4">
                                <div class="tooltip" data-tip="View permissions">
                                    <SecondaryButton
                                        aria-label="Permissions"
                                        @click="details(user)"
                                    >
                                        <ShieldCheckIcon class="w-5 h-5" />
                                    </SecondaryButton>
                                </div>
                                <div class="tooltip" data-tip="Edit user">
                                    <SecondaryButton
                                        aria-label="Edit"
                                        @click="edit(user)"
                                    >
                                        <PencilIcon class="w-5 h-5" />
                                    </SecondaryButton>
                                </div>
                                <div class="tooltip" data-tip="Delete user">
                                    <SecondaryButton
                                        aria-label="Delete"
                                        @click="remove(user)"
                                    >
                                        <TrashIcon class="w-5 h-5" />
                                    </SecondaryButton>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup lang="ts">
import { PencilIcon, TrashIcon, ShieldCheckIcon } from "@heroicons/vue/24/outline";
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { User } from "./../interfaces/User";
import { PropType } from "vue";
import dayjs from 'dayjs';

defineProps({
    users: {
        type: Array as PropType<User[]>,
        required: true,
    },
});

const emit = defineEmits(["edit", "remove", "details"]);

const edit = (user: User) => {
    emit("edit", user);
};

const remove = (user: User) => {
    emit("remove", user);
};

const details = (user: User) => {
    emit("details", user);
}
</script>
