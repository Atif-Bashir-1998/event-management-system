<template>
    <DefaultLayout title="Role Management">
        <p>Roles</p>
    </DefaultLayout>
</template>

<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue'

defineProps({
    roles: {
        type: Object,
        required: true,
    }
})
</script>
