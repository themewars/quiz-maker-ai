<?php

namespace App\Extensions\Contracts;

interface ExtensionInterface
{
    /**
     * Get the extension identifier
     */
    public function getId(): string;

    /**
     * Get the extension name
     */
    public function getName(): string;

    /**
     * Get the extension description
     */
    public function getDescription(): string;

    /**
     * Get the extension version
     */
    public function getVersion(): string;

    /**
     * Get the extension author
     */
    public function getAuthor(): string;

    /**
     * Get the extension dependencies
     */
    public function getDependencies(): array;

    /**
     * Check if extension is compatible with current system
     */
    public function isCompatible(): bool;

    /**
     * Install the extension
     */
    public function install(): bool;

    /**
     * Uninstall the extension
     */
    public function uninstall(): bool;

    /**
     * Activate the extension
     */
    public function activate(): bool;

    /**
     * Deactivate the extension
     */
    public function deactivate(): bool;

    /**
     * Check if extension is installed
     */
    public function isInstalled(): bool;

    /**
     * Check if extension is active
     */
    public function isActive(): bool;

    /**
     * Get extension configuration
     */
    public function getConfig(): array;

    /**
     * Set extension configuration
     */
    public function setConfig(array $config): bool;

    /**
     * Get extension hooks/events
     */
    public function getHooks(): array;

    /**
     * Register extension hooks
     */
    public function registerHooks(): void;

    /**
     * Unregister extension hooks
     */
    public function unregisterHooks(): void;

    /**
     * Get extension requirements
     */
    public function getRequirements(): array;

    /**
     * Validate extension requirements
     */
    public function validateRequirements(): bool;

    /**
     * Get extension permissions
     */
    public function getPermissions(): array;

    /**
     * Get extension menu items
     */
    public function getMenuItems(): array;

    /**
     * Get extension routes
     */
    public function getRoutes(): array;

    /**
     * Get extension migrations
     */
    public function getMigrations(): array;

    /**
     * Run extension migrations
     */
    public function runMigrations(): bool;

    /**
     * Rollback extension migrations
     */
    public function rollbackMigrations(): bool;

    /**
     * Get extension assets (CSS, JS)
     */
    public function getAssets(): array;

    /**
     * Get extension translations
     */
    public function getTranslations(): array;

    /**
     * Get extension settings schema
     */
    public function getSettingsSchema(): array;

    /**
     * Handle extension updates
     */
    public function update(string $fromVersion, string $toVersion): bool;

    /**
     * Get extension documentation
     */
    public function getDocumentation(): string;

    /**
     * Get extension changelog
     */
    public function getChangelog(): array;

    /**
     * Get extension support information
     */
    public function getSupportInfo(): array;
}
