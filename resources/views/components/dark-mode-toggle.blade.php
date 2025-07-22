<div
    x-data="darkModeToggle()"
    x-init="init()"
    class="fixed bottom-4 right-4 z-50"
    @keydown.window.escape="closeDropdown()"
>
    <button
        @click="toggleDarkMode()"
        :aria-pressed="darkMode.toString()"
        class="relative inline-flex h-12 w-20 items-center rounded-full bg-gray-300 dark:bg-gray-700 shadow-md transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
        role="switch"
        aria-label="Toggle dark mode"
        type="button"
        title="Toggle dark mode"
    >
        <span
            class="inline-block h-8 w-8 transform rounded-full bg-white shadow ring-0 transition-transform duration-300"
            :class="darkMode ? 'translate-x-10' : 'translate-x-1'"
        ></span>
    </button>
</div>
