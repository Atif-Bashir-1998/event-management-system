<template>
    <h1>Permission</h1>
</template>

<script setup lang="ts">
import { Role } from './interfaces/Role'
import { Permission } from './interfaces/Permission'
import { PropType } from "vue";


defineProps({
    roles: {
        type: Array as PropType<Role[]>,
        required: true,
    },
    permissions: {
        type: Array as PropType<Permission[]>,
        required: true,
    },
})

</script>
