<?php

namespace App\Models;

use App\Enums\ModuleMembershipStatus;
use App\Enums\RoleInModule;
use Database\Factories\ModuleMembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleMembership extends Model
{
    /** @use HasFactory<ModuleMembershipFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'module_id',
        'user_id',
        'role_in_module',
        'status',
        'added_by_user_id',
        'removed_by_user_id',
        'removed_at',
        'module_group_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ModuleGroup::class, 'module_group_id');
    }

    protected function casts(): array
    {
        return [
            'removed_at' => 'datetime',
            'status' => ModuleMembershipStatus::class,
            'role_in_module' => RoleInModule::class,
        ];
    }
}
