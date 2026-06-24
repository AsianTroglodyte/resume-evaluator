<?php

namespace App\Models;

use App\Enums\ModuleStatus;
use App\Enums\RoleInModule;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'created_by_user_id',
    ];

    protected function casts(): array 
    {
        return [
            'module_status' => ModuleStatus::class,
        ];
    }

    public function jobListings(): HasMany
    {
        return $this->hasMany(JobListing::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(ModuleMembership::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'module_memberships')
            ->withPivot('role_in_module', 'status', 'joined_at', 'removed_at')
            ->wherePivot('status', 'active');
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'module_memberships')
            ->withPivot('role_in_module', 'status', 'joined_at', 'removed_at')
            ->wherePivot('status', 'active')
            ->wherePivot('role_in_module', RoleInModule::Instructor->value);
    }

    // Module.php
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
