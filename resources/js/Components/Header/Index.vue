<template>
  <header class="z-10 py-4 shadow-md">
    <div class="container flex items-center justify-between h-full px-6 mx-auto">
      <!-- Mobile hamburger -->
      <button
        class="p-1 mr-5 -ml-1 rounded-md md:hidden focus:outline-none focus:shadow-outline-purple"
        aria-label="Menu"
        @click="sidebarStore.toggleSidebar"
      >
        <Bars3Icon class="h-6 w-6" />
      </button>

      <ul class="flex items-center justify-evenly md:justify-end w-full flex-shrink-0 space-x-6">
        <!-- Theme toggler -->
        <li class="flex">
          <button
            class="rounded-md"
            aria-label="Toggle color mode"
            @click="darkModeStore.toggleDarkMode"
          >
            <template v-if="darkModeStore.darkMode">
              <MoonIcon class="w-5 h-5" />
            </template>
            <template v-else>
              <SunIcon class="w-5 h-5" />
            </template>
          </button>
        </li>
        <!-- Notifications menu -->
        <li ref="notificationMenu" class="relative">
          <button
            class="relative align-middle rounded-md focus:outline-none focus:shadow-outline-purple"
            aria-label="Notifications"
            aria-haspopup="true"
            @click="toggleNotificationsMenu"
            @keydown.escape="closeNotificationsMenu"
          >
            <BellIcon class="w-5 h-5" />
            <span
              aria-hidden="true"
              class="absolute top-0 right-0 inline-block w-3 h-3 transform translate-x-1 -translate-y-1 bg-red-600 border-2 border-white rounded-full dark:border-gray-800"
            ></span>
          </button>
          <template v-if="isNotificationsMenuOpen">
            <ul
              class="absolute right-0 w-56 p-2 mt-2 space-y-2 text-gray-600 bg-white border border-gray-100 rounded-md shadow-md dark:text-gray-300 dark:border-gray-700 dark:bg-gray-700"
              @keydown.escape="closeNotificationsMenu"
            >
              <li class="flex">
                <a
                  class="inline-flex items-center justify-between w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                  href="#"
                >
                  <span>Messages</span>
                  <span
                    class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-600 bg-red-100 rounded-full dark:text-red-100 dark:bg-red-600"
                  >
                    13
                  </span>
                </a>
              </li>
              <li class="flex">
                <a
                  class="inline-flex items-center justify-between w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                  href="#"
                >
                  <span>Sales</span>
                  <span
                    class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-600 bg-red-100 rounded-full dark:text-red-100 dark:bg-red-600"
                  >
                    2
                  </span>
                </a>
              </li>
              <li class="flex">
                <a
                  class="inline-flex items-center justify-between w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                  href="#"
                >
                  <span>Alerts</span>
                </a>
              </li>
            </ul>
          </template>
        </li>

        <!-- Profile menu -->
        <li ref="profileMenu" class="relative">
          <button
            class="align-middle rounded-full focus:shadow-outline-purple focus:outline-none"
            aria-label="Account"
            aria-haspopup="true"
            @click="toggleProfileMenu"
            @keydown.escape="closeProfileMenu"
          >
            <img
              class="object-cover w-8 h-8 rounded-full"
              src="https://images.unsplash.com/photo-1502378735452-bc7d86632805?ixlib=rb-0.3.5&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=200&fit=max&s=aa3a807e1bbdfd4364d1f449eaa96d82"
              alt=""
              aria-hidden="true"
            />
          </button>
          <template v-if="isProfileMenuOpen">
            <ul
              class="absolute right-0 w-56 p-2 mt-2 space-y-2 rounded-md shadow-md bg-base-200"
              aria-label="submenu"
              @keydown.escape="closeProfileMenu"
            >
              <li class="flex">
                <Link
                  class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md link-custom"
                  :href="route('profile.edit')"
                >
                  <UserIcon class="w-4 h-4 mr-3" />
                  <span>Profile</span>
                </Link>
              </li>
              <li class="flex">
                <Link
                  class="inline-flex items-center w-full px-2 py-1 text-sm font-semibold transition-colors duration-150 rounded-md link-custom"
                  :href="route('logout')"
                  method="post"
                  as="button"
                >
                  <ArrowLeftOnRectangleIcon class="w-4 h-4 mr-3" />
                  <span>Log Out</span>
                </Link>
              </li>
            </ul>
          </template>
        </li>
      </ul>
    </div>
  </header>
</template>

<script setup>
  import {
    Bars3Icon,
    SunIcon,
    MoonIcon,
    BellIcon,
    UserIcon,
    ArrowLeftOnRectangleIcon,
  } from '@heroicons/vue/24/outline'
  import { useSidebarStore } from '@/Stores/sidebar'
  import { useDarkModeStore } from '@/Stores/darkMode'
  import { ref } from 'vue'
  import { onClickOutside } from '@vueuse/core'
  import { Link, usePage } from '@inertiajs/vue3'

  const sidebarStore = useSidebarStore()
  const darkModeStore = useDarkModeStore()

  defineOptions({
    name: 'Header',
  })

  const isNotificationsMenuOpen = ref(false)
  const isProfileMenuOpen = ref(false)
  const profileMenu = ref(null)
  const notificationMenu = ref(null)

  const toggleProfileMenu = () => {
    isProfileMenuOpen.value = !isProfileMenuOpen.value
  }

  const closeNotificationsMenu = () => {
    isNotificationsMenuOpen.value = false
  }

  const closeProfileMenu = () => {
    isProfileMenuOpen.value = false
  }

  onClickOutside(profileMenu, () => {
    closeProfileMenu()
  })

  onClickOutside(notificationMenu, () => {
    closeNotificationsMenu()
  })
</script>
