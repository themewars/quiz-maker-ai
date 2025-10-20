<?php

namespace App\Services;

use App\Extensions\Contracts\ExtensionInterface;
use App\Extensions\Extension;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ExtensionManager
{
    protected array $extensions = [];
    protected array $loadedExtensions = [];
    protected string $extensionsPath;
    protected string $cacheKey = 'extensions_registry';

    public function __construct()
    {
        $this->extensionsPath = app_path('Extensions');
        $this->loadExtensions();
    }

    /**
     * Load all available extensions
     */
    public function loadExtensions(): void
    {
        try {
            $this->extensions = Cache::remember($this->cacheKey, 3600, function () {
                return $this->discoverExtensions();
            });
        } catch (\Exception $e) {
            Log::error("Failed to load extensions: " . $e->getMessage());
            $this->extensions = [];
        }
    }

    /**
     * Discover extensions from filesystem
     */
    protected function discoverExtensions(): array
    {
        $extensions = [];
        
        if (!File::exists($this->extensionsPath)) {
            return $extensions;
        }

        $directories = File::directories($this->extensionsPath);
        
        foreach ($directories as $directory) {
            $extensionName = basename($directory);
            $extensionClass = "App\\Extensions\\{$extensionName}\\{$extensionName}Extension";
            
            if (class_exists($extensionClass)) {
                try {
                    $extension = new $extensionClass();
                    if ($extension instanceof ExtensionInterface) {
                        $extensions[$extension->getId()] = [
                            'class' => $extensionClass,
                            'path' => $directory,
                            'info' => [
                                'id' => $extension->getId(),
                                'name' => $extension->getName(),
                                'description' => $extension->getDescription(),
                                'version' => $extension->getVersion(),
                                'author' => $extension->getAuthor(),
                                'dependencies' => $extension->getDependencies(),
                                'requirements' => $extension->getRequirements(),
                            ]
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to load extension {$extensionName}: " . $e->getMessage());
                }
            }
        }

        return $extensions;
    }

    /**
     * Get all available extensions
     */
    public function getAllExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Get installed extensions
     */
    public function getInstalledExtensions(): Collection
    {
        return collect($this->extensions)->filter(function ($extension) {
            return $this->isInstalled($extension['info']['id']);
        });
    }

    /**
     * Get active extensions
     */
    public function getActiveExtensions(): Collection
    {
        return collect($this->extensions)->filter(function ($extension) {
            return $this->isActive($extension['info']['id']);
        });
    }

    /**
     * Get extension by ID
     */
    public function getExtension(string $id): ?ExtensionInterface
    {
        if (!isset($this->extensions[$id])) {
            return null;
        }

        if (!isset($this->loadedExtensions[$id])) {
            try {
                $extensionClass = $this->extensions[$id]['class'];
                $this->loadedExtensions[$id] = new $extensionClass();
            } catch (\Exception $e) {
                Log::error("Failed to instantiate extension {$id}: " . $e->getMessage());
                return null;
            }
        }

        return $this->loadedExtensions[$id];
    }

    /**
     * Install extension
     */
    public function installExtension(string $id): bool
    {
        $extension = $this->getExtension($id);
        
        if (!$extension) {
            Log::error("Extension {$id} not found");
            return false;
        }

        if ($extension->isInstalled()) {
            Log::warning("Extension {$id} is already installed");
            return true;
        }

        if (!$extension->isCompatible()) {
            Log::error("Extension {$id} is not compatible");
            return false;
        }

        $result = $extension->install();
        
        if ($result) {
            $this->clearCache();
            Log::info("Extension {$id} installed successfully");
        }

        return $result;
    }

    /**
     * Uninstall extension
     */
    public function uninstallExtension(string $id): bool
    {
        $extension = $this->getExtension($id);
        
        if (!$extension) {
            Log::error("Extension {$id} not found");
            return false;
        }

        if (!$extension->isInstalled()) {
            Log::warning("Extension {$id} is not installed");
            return true;
        }

        $result = $extension->uninstall();
        
        if ($result) {
            $this->clearCache();
            Log::info("Extension {$id} uninstalled successfully");
        }

        return $result;
    }

    /**
     * Activate extension
     */
    public function activateExtension(string $id): bool
    {
        $extension = $this->getExtension($id);
        
        if (!$extension) {
            Log::error("Extension {$id} not found");
            return false;
        }

        if (!$extension->isInstalled()) {
            Log::error("Extension {$id} is not installed");
            return false;
        }

        $result = $extension->activate();
        
        if ($result) {
            $this->clearCache();
            Log::info("Extension {$id} activated successfully");
        }

        return $result;
    }

    /**
     * Deactivate extension
     */
    public function deactivateExtension(string $id): bool
    {
        $extension = $this->getExtension($id);
        
        if (!$extension) {
            Log::error("Extension {$id} not found");
            return false;
        }

        $result = $extension->deactivate();
        
        if ($result) {
            $this->clearCache();
            Log::info("Extension {$id} deactivated successfully");
        }

        return $result;
    }

    /**
     * Check if extension is installed
     */
    public function isInstalled(string $id): bool
    {
        $extension = DB::table('extensions')->where('id', $id)->first();
        return $extension && $extension->installed;
    }

    /**
     * Check if extension is active
     */
    public function isActive(string $id): bool
    {
        $extension = DB::table('extensions')->where('id', $id)->first();
        return $extension && $extension->active;
    }

    /**
     * Get extension status
     */
    public function getExtensionStatus(string $id): array
    {
        $extension = DB::table('extensions')->where('id', $id)->first();
        
        if (!$extension) {
            return [
                'installed' => false,
                'active' => false,
                'version' => null,
                'config' => []
            ];
        }

        return [
            'installed' => (bool) $extension->installed,
            'active' => (bool) $extension->active,
            'version' => $extension->version,
            'config' => json_decode($extension->config ?? '{}', true)
        ];
    }

    /**
     * Update extension configuration
     */
    public function updateExtensionConfig(string $id, array $config): bool
    {
        $extension = $this->getExtension($id);
        
        if (!$extension) {
            Log::error("Extension {$id} not found");
            return false;
        }

        return $extension->setConfig($config);
    }

    /**
     * Get extension configuration
     */
    public function getExtensionConfig(string $id): array
    {
        $extension = $this->getExtension($id);
        
        if (!$extension) {
            return [];
        }

        return $extension->getConfig();
    }

    /**
     * Check extension dependencies
     */
    public function checkDependencies(string $id): array
    {
        $extension = $this->getExtension($id);
        
        if (!$extension) {
            return ['error' => 'Extension not found'];
        }

        $dependencies = $extension->getDependencies();
        $results = [];

        foreach ($dependencies as $dependency) {
            $results[$dependency] = $this->isInstalled($dependency);
        }

        return $results;
    }

    /**
     * Get extension requirements status
     */
    public function getRequirementsStatus(string $id): array
    {
        $extension = $this->getExtension($id);
        
        if (!$extension) {
            return ['error' => 'Extension not found'];
        }

        $requirements = $extension->getRequirements();
        $results = [];

        foreach ($requirements as $requirement) {
            $results[] = [
                'requirement' => $requirement,
                'met' => $this->checkRequirement($requirement)
            ];
        }

        return $results;
    }

    /**
     * Check individual requirement
     */
    protected function checkRequirement(array $requirement): bool
    {
        $type = $requirement['type'] ?? 'php_version';
        
        switch ($type) {
            case 'php_version':
                return version_compare(PHP_VERSION, $requirement['version'], '>=');
            case 'laravel_version':
                return version_compare(app()->version(), $requirement['version'], '>=');
            case 'extension':
                return extension_loaded($requirement['name']);
            case 'file':
                return File::exists($requirement['path']);
            default:
                return true;
        }
    }

    /**
     * Get all extension menu items
     */
    public function getAllMenuItems(): array
    {
        $menuItems = [];
        
        foreach ($this->getActiveExtensions() as $extension) {
            $extensionInstance = $this->getExtension($extension['info']['id']);
            if ($extensionInstance) {
                $items = $extensionInstance->getMenuItems();
                $menuItems = array_merge($menuItems, $items);
            }
        }

        return $menuItems;
    }

    /**
     * Get all extension routes
     */
    public function getAllRoutes(): array
    {
        $routes = [];
        
        foreach ($this->getActiveExtensions() as $extension) {
            $extensionInstance = $this->getExtension($extension['info']['id']);
            if ($extensionInstance) {
                $extensionRoutes = $extensionInstance->getRoutes();
                $routes = array_merge($routes, $extensionRoutes);
            }
        }

        return $routes;
    }

    /**
     * Get all extension assets
     */
    public function getAllAssets(): array
    {
        $assets = [];
        
        foreach ($this->getActiveExtensions() as $extension) {
            $extensionInstance = $this->getExtension($extension['info']['id']);
            if ($extensionInstance) {
                $extensionAssets = $extensionInstance->getAssets();
                $assets = array_merge($assets, $extensionAssets);
            }
        }

        return $assets;
    }

    /**
     * Clear extension cache
     */
    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
        $this->extensions = [];
        $this->loadedExtensions = [];
        $this->loadExtensions();
    }

    /**
     * Refresh extensions registry
     */
    public function refreshRegistry(): void
    {
        $this->clearCache();
        Log::info("Extension registry refreshed");
    }

    /**
     * Get extension statistics
     */
    public function getStatistics(): array
    {
        $total = count($this->extensions);
        $installed = $this->getInstalledExtensions()->count();
        $active = $this->getActiveExtensions()->count();

        return [
            'total' => $total,
            'installed' => $installed,
            'active' => $active,
            'available' => $total - $installed
        ];
    }

    /**
     * Validate extension integrity
     */
    public function validateExtension(string $id): array
    {
        $extension = $this->getExtension($id);
        
        if (!$extension) {
            return ['valid' => false, 'error' => 'Extension not found'];
        }

        $issues = [];

        // Check compatibility
        if (!$extension->isCompatible()) {
            $issues[] = 'Not compatible with current system';
        }

        // Check dependencies
        $dependencies = $this->checkDependencies($id);
        foreach ($dependencies as $dep => $installed) {
            if (!$installed) {
                $issues[] = "Missing dependency: {$dep}";
            }
        }

        // Check requirements
        $requirements = $this->getRequirementsStatus($id);
        foreach ($requirements as $req) {
            if (!$req['met']) {
                $issues[] = "Requirement not met: " . json_encode($req['requirement']);
            }
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues
        ];
    }
}
