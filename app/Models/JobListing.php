<?php

namespace App\Models;

use App\Enums\JobListingSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'module_id',
        'name',
        'description',
    ];

    public function casts() : array 
    {
        return [
            'job_listing_source' => JobListingSource::class
        ];
    }
}
