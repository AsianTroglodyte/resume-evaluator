<?php

namespace App\Models;

use App\Enums\ModuleMembershipStatus;
use Database\Factories\ModuleGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleGroup extends Model
{
    /** @use HasFactory<ModuleGroupFactory> */
    use HasFactory;

    protected $fillable = [
        'module_id',
        'name',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(ModuleMembership::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'module_memberships', 'module_group_id', 'user_id')
            ->wherePivot('status', ModuleMembershipStatus::Active->value);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}
