<?php

namespace App\Models;

use Database\Factories\GroupMembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMembership extends Model
{
    /** @use HasFactory<GroupMembershipFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'group_id',
        'user_id',
        'role_in_group',
        'status',
        'added_by_user_id',
        'removed_by_user_id'
    ];
}



