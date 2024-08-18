<template>
    <li class="relative px-6 py-3">
        <span v-if="isActive" class="absolute inset-y-0 left-0 w-1 bg-primary rounded-tr-lg rounded-br-lg"
            aria-hidden="true"></span>
        <Link class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150" :class="isActive
                ? 'text-primary hover:text-primary-focus'
                : 'text-base-content hover:text-accent'
            " :href="route(item.routeName)">
            <component :is="item.icon" class="h-5 w-5" />
            <span class="ml-4">{{ item.label }}</span>
        </Link>
    </li>
</template>

<script setup lang="ts">
import { PropType, computed } from "vue";
import { Link } from "@inertiajs/vue3";

type SidebarItem = {
    routeName: string;
    label: string;
    icon: HTMLElement;
};

const props = defineProps({
    item: {
        type: Object as PropType<SidebarItem>,
        required: true,
    },
});

const isActive = computed(() => route().current(props.item.routeName));
</script>
