<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use App\Models\JobListing;
use App\Models\Assignment;

class Group extends Model
{
    //
    use HasFactory;

    public function jobListings(): HasMany
    {
        return $this->hasMany(JobListing::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }
}
