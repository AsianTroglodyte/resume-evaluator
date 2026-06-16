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
}
