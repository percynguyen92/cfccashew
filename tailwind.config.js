import daisyui from "daisyui";
import themes from "daisyui/theme/object";

export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./node_modules/daisyui/dist/**/*.js",
    ],
    plugins: [daisyui],
    daisyui: {
        themes: ["light", "dark"],
    },
};
