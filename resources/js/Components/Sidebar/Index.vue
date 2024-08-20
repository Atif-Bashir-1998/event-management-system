<template>
    <!-- Desktop sidebar -->
    <aside
        class="z-20 w-64 overflow-y-auto bg-base-300 md:!block hidden flex-shrink-0"
    >
        <div class="py-2">
            <div
                class="text-lg font-bold flex justify-center"
            >
                <img v-if="darkModeStore.darkMode" :src="DarkLogo" class="h-12 w-auto" />
                <img v-else :src="LightLogo" class="h-12 w-auto" />
            </div>
            <ul class="mt-6">
                <SidebarItem :item="{routeName: 'dashboard', label: 'Dashboard', icon: HomeIcon}" />
            </ul>
            <ul>
                <SidebarItem :item="{routeName: 'role.index', label: 'Role Management', icon: UserGroupIcon}" />
            </ul>
        </div>
    </aside>
    <!-- Mobile sidebar -->
    <!-- Backdrop -->
    <Transition name="overlay">
        <div
            v-show="sidebarStore.getIsSidebarOpen"
            class="fixed inset-0 z-10 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center"
            @click="sidebarStore.toggleSidebar"
        ></div>
    </Transition>
    <Transition name="sidebar">
        <aside
            v-show="sidebarStore.isSidebarOpen"
            class="fixed inset-y-0 z-20 flex-shrink-0 w-64 mt-16 overflow-y-auto bg-base-300 md:hidden"
        >
            <div class="py-4 text-gray-500 dark:text-gray-400">
                <a
                    class="ml-6 text-lg font-bold"
                    href="#"
                >
                    <img v-if="darkModeStore.darkMode" :src="DarkLogo" class="w-24" />
                    <img v-else :src="LightLogo" class="w-24" />
                </a>
                <ul class="mt-6">
                    <SidebarItem :item="{routeName: 'dashboard', label: 'Dashboard', icon: HomeIcon}" />
                </ul>
                <ul>
                    <SidebarItem :item="{routeName: 'role.index', label: 'Role Management', icon: UserGroupIcon}" />
                </ul>
            </div>
        </aside>
    </Transition>
</template>

<script setup>
// import DarkLogo from "@/assets/images/logo/dark-logo.png";
import DarkLogo from "@/assets/images/logo/vue-laravel.webp";
import LightLogo from "@/assets/images/logo/vue-laravel.webp";
import {
    HomeIcon,
    UserGroupIcon,
} from "@heroicons/vue/24/outline";
import { useSidebarStore } from "@/Stores/sidebar";
import { useDarkModeStore } from "@/Stores/darkMode";
import { onMounted } from "vue";
import SidebarItem from "./SidebarItem.vue"

const sidebarStore = useSidebarStore();
const darkModeStore = useDarkModeStore();


defineOptions({
    name: 'Sidebar'
})

onMounted(() => {
    sidebarStore.closeSidebar()
})

</script>

<style>
.overlay-enter-active,
.overlay-leave-active {
    @apply transition ease-in-out duration-150;
}

.overlay-enter-from,
.overlay-leave-to {
    @apply opacity-0;
}

.overlay-enter-to,
.overlay-leave-from {
    @apply opacity-100;
}

.sidebar-enter-active,
.sidebar-leave-active {
    @apply transition ease-in-out duration-150;
}

.sidebar-enter-from,
.sidebar-leave-to {
    @apply opacity-0 transform -translate-x-20;
}

.sidebar-enter-to,
.sidebar-leave-from {
    @apply opacity-100;
}
</style>
