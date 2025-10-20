<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ExtensionManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class ExtensionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ExtensionManager::class, function ($app) {
            return new ExtensionManager();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadExtensionRoutes();
        $this->loadExtensionViews();
        $this->loadExtensionTranslations();
        $this->loadExtensionAssets();
        $this->registerExtensionHooks();
    }

    /**
     * Load extension routes
     */
    protected function loadExtensionRoutes(): void
    {
        try {
            $extensionManager = app(ExtensionManager::class);
            $routes = $extensionManager->getAllRoutes();
            
            foreach ($routes as $route) {
                Route::{$route['method']}($route['uri'], $route['action'])
                    ->middleware($route['middleware'] ?? []);
            }
        } catch (\Exception $e) {
            // Log error but don't break the application
            \Log::error("Failed to load extension routes: " . $e->getMessage());
        }
    }

    /**
     * Load extension views
     */
    protected function loadExtensionViews(): void
    {
        try {
            $extensionManager = app(ExtensionManager::class);
            $activeExtensions = $extensionManager->getActiveExtensions();
            
            foreach ($activeExtensions as $extension) {
                $extensionInstance = $extensionManager->getExtension($extension['info']['id']);
                if ($extensionInstance) {
                    $path = $extension['path'] . '/views';
                    if (file_exists($path)) {
                        $this->loadViewsFrom($path, $extension['info']['id']);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error("Failed to load extension views: " . $e->getMessage());
        }
    }

    /**
     * Load extension translations
     */
    protected function loadExtensionTranslations(): void
    {
        try {
            $extensionManager = app(ExtensionManager::class);
            $activeExtensions = $extensionManager->getActiveExtensions();
            
            foreach ($activeExtensions as $extension) {
                $extensionInstance = $extensionManager->getExtension($extension['info']['id']);
                if ($extensionInstance) {
                    $translations = $extensionInstance->getTranslations();
                    
                    foreach ($translations as $locale => $translationData) {
                        $path = $extension['path'] . "/lang/{$locale}";
                        if (file_exists($path)) {
                            $this->loadTranslationsFrom($path, $extension['info']['id']);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error("Failed to load extension translations: " . $e->getMessage());
        }
    }

    /**
     * Load extension assets
     */
    protected function loadExtensionAssets(): void
    {
        try {
            $extensionManager = app(ExtensionManager::class);
            $assets = $extensionManager->getAllAssets();
            
            // Share assets with views
            View::share('extensionAssets', $assets);
        } catch (\Exception $e) {
            \Log::error("Failed to load extension assets: " . $e->getMessage());
        }
    }

    /**
     * Register extension hooks
     */
    protected function registerExtensionHooks(): void
    {
        try {
            $extensionManager = app(ExtensionManager::class);
            $activeExtensions = $extensionManager->getActiveExtensions();
            
            foreach ($activeExtensions as $extension) {
                $extensionInstance = $extensionManager->getExtension($extension['info']['id']);
                if ($extensionInstance) {
                    $extensionInstance->registerHooks();
                }
            }
        } catch (\Exception $e) {
            \Log::error("Failed to register extension hooks: " . $e->getMessage());
        }
    }
}
