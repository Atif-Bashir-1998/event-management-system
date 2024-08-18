<template>
    <div class="min-h-screen bg-base-200 flex items-center justify-center">
        <div class="max-w-4xl p-8 bg-white rounded-lg shadow-lg text-center">
            <h1 class="text-4xl font-bold text-primary">Welcome to Event Management System</h1>
            <p class="mt-4 text-lg text-gray-600">
                Manage and organize your events effortlessly. Create, manage, and track your events all in one place.
            </p>

            <div class="mt-4">
                <span class="text-sm text-gray-500">This is a demo/practice project. You can view the source code and
                    contribute on GitHub.</span>
            </div>

            <div class="mt-8 flex justify-center">
                <router-link to="/events" class="btn btn-primary mr-4">View Events</router-link>
                <router-link to="/login" class="btn btn-secondary">Get Started</router-link>
            </div>

            <div class="mt-8 text-center">
                <a href="#" target="_blank" class="text-blue-500 hover:underline">
                    View Source Code on GitHub
                </a>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
</script>

<style scoped>
/* Add any additional custom styles here */
</style>
