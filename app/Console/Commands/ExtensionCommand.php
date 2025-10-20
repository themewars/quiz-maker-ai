<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExtensionManager;
use App\Models\Extension;

class ExtensionCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'extension:manage 
                            {action : The action to perform (list, install, uninstall, activate, deactivate, refresh)}
                            {extension? : The extension ID (required for install, uninstall, activate, deactivate)}';

    /**
     * The console command description.
     */
    protected $description = 'Manage extensions from command line';

    /**
     * Execute the console command.
     */
    public function handle(ExtensionManager $extensionManager): int
    {
        $action = $this->argument('action');
        $extensionId = $this->argument('extension');

        switch ($action) {
            case 'list':
                return $this->listExtensions($extensionManager);
            case 'install':
                return $this->installExtension($extensionManager, $extensionId);
            case 'uninstall':
                return $this->uninstallExtension($extensionManager, $extensionId);
            case 'activate':
                return $this->activateExtension($extensionManager, $extensionId);
            case 'deactivate':
                return $this->deactivateExtension($extensionManager, $extensionId);
            case 'refresh':
                return $this->refreshRegistry($extensionManager);
            default:
                $this->error("Unknown action: {$action}");
                return 1;
        }
    }

    /**
     * List all extensions
     */
    protected function listExtensions(ExtensionManager $extensionManager): int
    {
        $this->info('Available Extensions:');
        $this->newLine();

        $extensions = $extensionManager->getAllExtensions();
        
        if (empty($extensions)) {
            $this->warn('No extensions found.');
            return 0;
        }

        $headers = ['ID', 'Name', 'Version', 'Author', 'Status', 'Installed', 'Active'];
        $rows = [];

        foreach ($extensions as $id => $extension) {
            $status = $extensionManager->getExtensionStatus($id);
            
            $rows[] = [
                $id,
                $extension['info']['name'],
                $extension['info']['version'],
                $extension['info']['author'] ?? 'Unknown',
                $status['installed'] ? ($status['active'] ? 'Active' : 'Installed') : 'Available',
                $status['installed'] ? 'Yes' : 'No',
                $status['active'] ? 'Yes' : 'No'
            ];
        }

        $this->table($headers, $rows);

        // Show statistics
        $stats = $extensionManager->getStatistics();
        $this->newLine();
        $this->info('Statistics:');
        $this->line("Total: {$stats['total']}");
        $this->line("Installed: {$stats['installed']}");
        $this->line("Active: {$stats['active']}");
        $this->line("Available: {$stats['available']}");

        return 0;
    }

    /**
     * Install an extension
     */
    protected function installExtension(ExtensionManager $extensionManager, ?string $extensionId): int
    {
        if (!$extensionId) {
            $this->error('Extension ID is required for installation.');
            return 1;
        }

        $this->info("Installing extension: {$extensionId}");

        // Check if extension exists
        $extension = $extensionManager->getExtension($extensionId);
        if (!$extension) {
            $this->error("Extension '{$extensionId}' not found.");
            return 1;
        }

        // Check if already installed
        if ($extension->isInstalled()) {
            $this->warn("Extension '{$extensionId}' is already installed.");
            return 0;
        }

        // Check compatibility
        if (!$extension->isCompatible()) {
            $this->error("Extension '{$extensionId}' is not compatible with the current system.");
            return 1;
        }

        // Install
        if ($extensionManager->installExtension($extensionId)) {
            $this->info("Extension '{$extensionId}' installed successfully.");
            return 0;
        } else {
            $this->error("Failed to install extension '{$extensionId}'.");
            return 1;
        }
    }

    /**
     * Uninstall an extension
     */
    protected function uninstallExtension(ExtensionManager $extensionManager, ?string $extensionId): int
    {
        if (!$extensionId) {
            $this->error('Extension ID is required for uninstallation.');
            return 1;
        }

        $this->info("Uninstalling extension: {$extensionId}");

        // Check if extension exists
        $extension = $extensionManager->getExtension($extensionId);
        if (!$extension) {
            $this->error("Extension '{$extensionId}' not found.");
            return 1;
        }

        // Check if installed
        if (!$extension->isInstalled()) {
            $this->warn("Extension '{$extensionId}' is not installed.");
            return 0;
        }

        // Uninstall
        if ($extensionManager->uninstallExtension($extensionId)) {
            $this->info("Extension '{$extensionId}' uninstalled successfully.");
            return 0;
        } else {
            $this->error("Failed to uninstall extension '{$extensionId}'.");
            return 1;
        }
    }

    /**
     * Activate an extension
     */
    protected function activateExtension(ExtensionManager $extensionManager, ?string $extensionId): int
    {
        if (!$extensionId) {
            $this->error('Extension ID is required for activation.');
            return 1;
        }

        $this->info("Activating extension: {$extensionId}");

        // Check if extension exists
        $extension = $extensionManager->getExtension($extensionId);
        if (!$extension) {
            $this->error("Extension '{$extensionId}' not found.");
            return 1;
        }

        // Check if installed
        if (!$extension->isInstalled()) {
            $this->error("Extension '{$extensionId}' must be installed before activation.");
            return 1;
        }

        // Check if already active
        if ($extension->isActive()) {
            $this->warn("Extension '{$extensionId}' is already active.");
            return 0;
        }

        // Activate
        if ($extensionManager->activateExtension($extensionId)) {
            $this->info("Extension '{$extensionId}' activated successfully.");
            return 0;
        } else {
            $this->error("Failed to activate extension '{$extensionId}'.");
            return 1;
        }
    }

    /**
     * Deactivate an extension
     */
    protected function deactivateExtension(ExtensionManager $extensionManager, ?string $extensionId): int
    {
        if (!$extensionId) {
            $this->error('Extension ID is required for deactivation.');
            return 1;
        }

        $this->info("Deactivating extension: {$extensionId}");

        // Check if extension exists
        $extension = $extensionManager->getExtension($extensionId);
        if (!$extension) {
            $this->error("Extension '{$extensionId}' not found.");
            return 1;
        }

        // Check if active
        if (!$extension->isActive()) {
            $this->warn("Extension '{$extensionId}' is not active.");
            return 0;
        }

        // Deactivate
        if ($extensionManager->deactivateExtension($extensionId)) {
            $this->info("Extension '{$extensionId}' deactivated successfully.");
            return 0;
        } else {
            $this->error("Failed to deactivate extension '{$extensionId}'.");
            return 1;
        }
    }

    /**
     * Refresh extension registry
     */
    protected function refreshRegistry(ExtensionManager $extensionManager): int
    {
        $this->info('Refreshing extension registry...');

        $extensionManager->refreshRegistry();

        $this->info('Extension registry refreshed successfully.');
        return 0;
    }
}
