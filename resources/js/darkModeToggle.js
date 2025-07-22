export default function darkModeToggle() {
    return {
        darkMode: false,

        init() {
            this.darkMode =
                localStorage.getItem("darkMode") === "true" ||
                (!localStorage.getItem("darkMode") &&
                    window.matchMedia("(prefers-color-scheme: dark)").matches);
            this.applyDarkMode();
        },

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
    };
}
