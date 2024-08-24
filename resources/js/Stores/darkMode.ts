import { ref } from 'vue'
import { defineStore } from 'pinia'
import { useStorage } from '@vueuse/core'

export const useDarkModeStore = defineStore('darkMode', () => {
  const darkMode = useStorage('darkMode', ref(false))

  function toggleDarkMode() {
    darkMode.value = !darkMode.value

    implementChange()
  }

  function implementChange() {
    const htmlElement = document.documentElement

    if (darkMode.value) {
      htmlElement.dataset.theme = 'dark'
    } else {
      htmlElement.dataset.theme = 'light'
    }
  }

  implementChange()

  return { darkMode, toggleDarkMode }
})
