<?php

namespace App\Models;

use Database\Factories\ModuleMembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];
}
