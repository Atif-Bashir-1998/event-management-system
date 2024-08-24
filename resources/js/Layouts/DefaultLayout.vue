<script setup lang="ts">
import Header from "@/Components/Header/Index.vue";
import Sidebar from "@/Components/Sidebar/Index.vue";
import { useSidebarStore } from "@/Stores/sidebar";

defineProps({
    title: {
        type: String,
        default: "",
    },
});

const sidebarStore = useSidebarStore();
</script>

<template>
    <div
        class="flex h-screen overflow-x-hidden"
        :class="{ 'overflow-hidden': sidebarStore.isSidebarOpen }"
    >
        <Sidebar />
        <div class="flex flex-col flex-1 w-full">
            <Header />
            <main class="h-full overflow-y-auto">
                <div class="container px-6 mx-auto grid">
                    <h2
                        class="my-2 md:my-6 text-md md:text-2xl font-semibold"
                    >
                        {{ title }}
                    </h2>

                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
