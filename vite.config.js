import { defineConfig } from "vite";
import laravel, { refreshPaths } from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/home.css",
                "resources/css/app.css",
                "resources/css/filament/admin/theme.css",
                "resources/css/filament/user/theme.css",
                "resources/css/admin.scss",
                "resources/css/demo.scss",
                "resources/css/login.css",
                "resources/js/app.js",
                "resources/assets/js/pages.js",
                "resources/js/razorpay-checkout.js",
            ],
            refresh: [...refreshPaths, "app/Livewire/**", "app/Filament/**"],
        }),
    ],
});
