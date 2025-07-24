export default {
    data() {
        return {
            darkMode: false,
        };
    },
    mounted() {
        this.darkMode =
            localStorage.getItem("darkMode") === "true" ||
            (!localStorage.getItem("darkMode") &&
                window.matchMedia("(prefers-color-scheme: dark)").matches);
        this.applyDarkMode();
    },
    methods: {
        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            this.applyDarkMode();
            localStorage.setItem("darkMode", this.darkMode);
        },
        applyDarkMode() {
            if (this.darkMode) {
                document.documentElement.setAttribute("data-theme", "dark");
            } else {
                document.documentElement.setAttribute("data-theme", "light");
            }
        },
    },
    template: `
        <div class="fixed bottom-4 right-4 z-50">
            <button
                @click="toggleDarkMode"
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
    `,
};
