<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\JobListing;

class Group extends Model
{
    //
    use HasFactory;

    public function assignments() 
    {
        return $this->hasMany(JobListing::class);
    }
}
