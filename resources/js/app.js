import "./bootstrap";
import Alpine from "alpinejs";
import "./bills";
import darkModeToggle from "./darkModeToggle";

window.Alpine = Alpine;

Alpine.data("darkModeToggle", darkModeToggle);

Alpine.start();
