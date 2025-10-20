<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Extension extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'version',
        'description',
        'author',
        'dependencies',
        'requirements',
        'permissions',
        'config',
        'installed',
        'active',
        'installed_at',
        'activated_at'
    ];

    protected $casts = [
        'dependencies' => 'array',
        'requirements' => 'array',
        'permissions' => 'array',
        'config' => 'array',
        'installed' => 'boolean',
        'active' => 'boolean',
        'installed_at' => 'datetime',
        'activated_at' => 'datetime'
    ];

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Scope for installed extensions
     */
    public function scopeInstalled($query)
    {
        return $query->where('installed', true);
    }

    /**
     * Scope for active extensions
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope for available extensions
     */
    public function scopeAvailable($query)
    {
        return $query->where('installed', false);
    }

    /**
     * Get extension status
     */
    public function getStatusAttribute(): string
    {
        if ($this->active) {
            return 'active';
        } elseif ($this->installed) {
            return 'installed';
        } else {
            return 'available';
        }
    }

    /**
     * Get extension status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'success',
            'installed' => 'warning',
            'available' => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Check if extension is compatible
     */
    public function isCompatible(): bool
    {
        $requirements = $this->requirements ?? [];
        
        foreach ($requirements as $requirement) {
            if (!$this->checkRequirement($requirement)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Check individual requirement
     */
    protected function checkRequirement(array $requirement): bool
    {
        $type = $requirement['type'] ?? 'php_version';
        
        return match($type) {
            'php_version' => version_compare(PHP_VERSION, $requirement['version'], '>='),
            'laravel_version' => version_compare(app()->version(), $requirement['version'], '>='),
            'extension' => extension_loaded($requirement['name']),
            'file' => file_exists($requirement['path']),
            default => true
        };
    }

    /**
     * Get extension dependencies status
     */
    public function getDependenciesStatus(): array
    {
        $dependencies = $this->dependencies ?? [];
        $status = [];
        
        foreach ($dependencies as $dependency) {
            $status[$dependency] = self::where('id', $dependency)->where('installed', true)->exists();
        }
        
        return $status;
    }

    /**
     * Check if all dependencies are met
     */
    public function hasAllDependencies(): bool
    {
        $dependenciesStatus = $this->getDependenciesStatus();
        return !in_array(false, $dependenciesStatus, true);
    }

    /**
     * Get extension requirements status
     */
    public function getRequirementsStatus(): array
    {
        $requirements = $this->requirements ?? [];
        $status = [];
        
        foreach ($requirements as $requirement) {
            $status[] = [
                'requirement' => $requirement,
                'met' => $this->checkRequirement($requirement)
            ];
        }
        
        return $status;
    }

    /**
     * Check if all requirements are met
     */
    public function hasAllRequirements(): bool
    {
        $requirementsStatus = $this->getRequirementsStatus();
        return !in_array(false, array_column($requirementsStatus, 'met'), true);
    }

    /**
     * Get extension validation status
     */
    public function getValidationStatus(): array
    {
        $issues = [];
        
        if (!$this->isCompatible()) {
            $issues[] = 'Not compatible with current system';
        }
        
        if (!$this->hasAllDependencies()) {
            $issues[] = 'Missing dependencies';
        }
        
        if (!$this->hasAllRequirements()) {
            $issues[] = 'Requirements not met';
        }
        
        return [
            'valid' => empty($issues),
            'issues' => $issues
        ];
    }

    /**
     * Get extension statistics
     */
    public static function getStatistics(): array
    {
        return [
            'total' => self::count(),
            'installed' => self::installed()->count(),
            'active' => self::active()->count(),
            'available' => self::available()->count()
        ];
    }

    /**
     * Get extension by status
     */
    public static function getByStatus(string $status): \Illuminate\Database\Eloquent\Collection
    {
        return match($status) {
            'active' => self::active()->get(),
            'installed' => self::installed()->where('active', false)->get(),
            'available' => self::available()->get(),
            default => self::all()
        };
    }

    /**
     * Search extensions
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('author', 'like', "%{$search}%");
        });
    }

    /**
     * Filter by author
     */
    public function scopeByAuthor($query, string $author)
    {
        return $query->where('author', $author);
    }

    /**
     * Filter by version
     */
    public function scopeByVersion($query, string $version)
    {
        return $query->where('version', $version);
    }

    /**
     * Get latest extensions
     */
    public function scopeLatest($query, int $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Get popular extensions (by installation count)
     */
    public function scopePopular($query, int $limit = 10)
    {
        return $query->orderBy('installed_at', 'desc')->limit($limit);
    }
}
