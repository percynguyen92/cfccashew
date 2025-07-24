import "./bootstrap";
import { createApp } from "vue";
import BillsApp from "./bills";
import DarkModeToggle from "./darkModeToggle";
const rootOptions = BillsApp();
rootOptions.mounted = rootOptions.init;

const app = createApp(rootOptions);
app.component("dark-mode-toggle", DarkModeToggle);

app.mount("#app");
