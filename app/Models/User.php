<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\AssigneeScope;
use App\Enums\GlobalRole;
use App\Enums\ModuleStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [

        'first_name',
        'last_name',
        'email',
        'password',
        'global_role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'global_role' => GlobalRole::class,
        ];
    }

    // public function assignments(): HasMany
    // {

    // }

    public function memberships(): HasMany
    {
        return $this->hasMany(ModuleMembership::class);
    }

    public function modulesPartOf(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'module_memberships')
            ->wherePivot('status', ModuleStatus::Active);
    }

    public function isGlobalAdmin(): bool
    {
        // return true;
        // print($this->global_role === GlobalRole::Admin);
        return $this->global_role === GlobalRole::Admin;
    }

    public function isInModule(Module $module): bool
    {
        return $module->users()->whereKey($this->id)->exists();
    }

    public function isInstructorInModule(Module $module): bool
    {
        return $module->instructors()->whereKey($this->id)->exists();
    }

    public function isGivenAssignment(Assignment $assignment): bool
    {
        if (! $this->isInModule($assignment->module)) {
            return false;
        }

        return $assignment->assignee_scope === AssigneeScope::Everyone
            || $assignment->assignees()->whereKey($this->id)->exists();
    }
}
