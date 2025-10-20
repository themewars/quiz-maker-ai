<?php

namespace App\Extensions;

use App\Extensions\Contracts\ExtensionInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

abstract class Extension implements ExtensionInterface
{
    protected string $id;
    protected string $name;
    protected string $description;
    protected string $version;
    protected string $author;
    protected array $dependencies = [];
    protected array $requirements = [];
    protected array $permissions = [];
    protected array $menuItems = [];
    protected array $routes = [];
    protected array $migrations = [];
    protected array $assets = [];
    protected array $translations = [];
    protected array $settingsSchema = [];
    protected array $hooks = [];
    protected array $config = [];
    protected bool $installed = false;
    protected bool $active = false;

    public function __construct()
    {
        $this->initializeExtension();
    }

    /**
     * Initialize extension with default values
     */
    abstract protected function initializeExtension(): void;

    /**
     * Get the extension identifier
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the extension name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the extension description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get the extension version
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Get the extension author
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * Get the extension dependencies
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * Check if extension is compatible with current system
     */
    public function isCompatible(): bool
    {
        try {
            return $this->validateRequirements();
        } catch (\Exception $e) {
            Log::error("Extension compatibility check failed for {$this->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Install the extension
     */
    public function install(): bool
    {
        try {
            DB::beginTransaction();

            // Check dependencies
            if (!$this->checkDependencies()) {
                throw new \Exception("Dependencies not met");
            }

            // Run migrations
            if (!$this->runMigrations()) {
                throw new \Exception("Migration failed");
            }

            // Register hooks
            $this->registerHooks();

            // Mark as installed
            $this->installed = true;
            $this->saveExtensionStatus();

            DB::commit();
            
            Log::info("Extension {$this->id} installed successfully");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Extension installation failed for {$this->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Uninstall the extension
     */
    public function uninstall(): bool
    {
        try {
            DB::beginTransaction();

            // Deactivate first
            $this->deactivate();

            // Unregister hooks
            $this->unregisterHooks();

            // Rollback migrations
            $this->rollbackMigrations();

            // Mark as uninstalled
            $this->installed = false;
            $this->saveExtensionStatus();

            DB::commit();
            
            Log::info("Extension {$this->id} uninstalled successfully");
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Extension uninstallation failed for {$this->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Activate the extension
     */
    public function activate(): bool
    {
        try {
            if (!$this->installed) {
                throw new \Exception("Extension not installed");
            }

            $this->active = true;
            $this->saveExtensionStatus();
            
            Log::info("Extension {$this->id} activated successfully");
            return true;

        } catch (\Exception $e) {
            Log::error("Extension activation failed for {$this->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deactivate the extension
     */
    public function deactivate(): bool
    {
        try {
            $this->active = false;
            $this->saveExtensionStatus();
            
            Log::info("Extension {$this->id} deactivated successfully");
            return true;

        } catch (\Exception $e) {
            Log::error("Extension deactivation failed for {$this->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if extension is installed
     */
    public function isInstalled(): bool
    {
        return $this->installed;
    }

    /**
     * Check if extension is active
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Get extension configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Set extension configuration
     */
    public function setConfig(array $config): bool
    {
        try {
            $this->config = array_merge($this->config, $config);
            $this->saveExtensionConfig();
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to set config for extension {$this->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get extension hooks/events
     */
    public function getHooks(): array
    {
        return $this->hooks;
    }

    /**
     * Register extension hooks
     */
    public function registerHooks(): void
    {
        foreach ($this->hooks as $hook => $callback) {
            if (is_callable($callback)) {
                // Register with Laravel's event system
                \Illuminate\Support\Facades\Event::listen($hook, $callback);
            }
        }
    }

    /**
     * Unregister extension hooks
     */
    public function unregisterHooks(): void
    {
        foreach ($this->hooks as $hook => $callback) {
            \Illuminate\Support\Facades\Event::forget($hook);
        }
    }

    /**
     * Get extension requirements
     */
    public function getRequirements(): array
    {
        return $this->requirements;
    }

    /**
     * Validate extension requirements
     */
    public function validateRequirements(): bool
    {
        foreach ($this->requirements as $requirement) {
            if (!$this->checkRequirement($requirement)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get extension permissions
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Get extension menu items
     */
    public function getMenuItems(): array
    {
        return $this->menuItems;
    }

    /**
     * Get extension routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Get extension migrations
     */
    public function getMigrations(): array
    {
        return $this->migrations;
    }

    /**
     * Run extension migrations
     */
    public function runMigrations(): bool
    {
        try {
            foreach ($this->migrations as $migration) {
                if (File::exists($migration)) {
                    Artisan::call('migrate', ['--path' => $migration]);
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error("Migration failed for extension {$this->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Rollback extension migrations
     */
    public function rollbackMigrations(): bool
    {
        try {
            foreach ($this->migrations as $migration) {
                if (File::exists($migration)) {
                    Artisan::call('migrate:rollback', ['--path' => $migration]);
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error("Migration rollback failed for extension {$this->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get extension assets (CSS, JS)
     */
    public function getAssets(): array
    {
        return $this->assets;
    }

    /**
     * Get extension translations
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    /**
     * Get extension settings schema
     */
    public function getSettingsSchema(): array
    {
        return $this->settingsSchema;
    }

    /**
     * Handle extension updates
     */
    public function update(string $fromVersion, string $toVersion): bool
    {
        try {
            // Override in child classes for specific update logic
            Log::info("Extension {$this->id} updated from {$fromVersion} to {$toVersion}");
            return true;
        } catch (\Exception $e) {
            Log::error("Extension update failed for {$this->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get extension documentation
     */
    public function getDocumentation(): string
    {
        return "No documentation available for {$this->name}";
    }

    /**
     * Get extension changelog
     */
    public function getChangelog(): array
    {
        return [
            $this->version => "Initial release"
        ];
    }

    /**
     * Get extension support information
     */
    public function getSupportInfo(): array
    {
        return [
            'author' => $this->author,
            'version' => $this->version,
            'documentation' => $this->getDocumentation(),
            'support_email' => null,
            'support_url' => null
        ];
    }

    /**
     * Check extension dependencies
     */
    protected function checkDependencies(): bool
    {
        foreach ($this->dependencies as $dependency) {
            if (!$this->isDependencyInstalled($dependency)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if dependency is installed
     */
    protected function isDependencyInstalled(string $dependency): bool
    {
        // Implement dependency checking logic
        return true; // Placeholder
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
     * Save extension status to database
     */
    protected function saveExtensionStatus(): void
    {
        DB::table('extensions')->updateOrInsert(
            ['id' => $this->id],
            [
                'name' => $this->name,
                'version' => $this->version,
                'installed' => $this->installed,
                'active' => $this->active,
                'updated_at' => now()
            ]
        );
    }

    /**
     * Save extension configuration
     */
    protected function saveExtensionConfig(): void
    {
        DB::table('extensions')->where('id', $this->id)->update([
            'config' => json_encode($this->config),
            'updated_at' => now()
        ]);
    }

    /**
     * Load extension status from database
     */
    protected function loadExtensionStatus(): void
    {
        $extension = DB::table('extensions')->where('id', $this->id)->first();
        
        if ($extension) {
            $this->installed = (bool) $extension->installed;
            $this->active = (bool) $extension->active;
            $this->config = json_decode($extension->config ?? '{}', true);
        }
    }
}
