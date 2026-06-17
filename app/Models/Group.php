<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany; 


class Group extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'created_by_user_id',
    ];

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
        return $this->hasMany(GroupMembership::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_memberships')
            ->withPivot('role_in_group', 'status', 'joined_at', 'removed_at');
    }
}
