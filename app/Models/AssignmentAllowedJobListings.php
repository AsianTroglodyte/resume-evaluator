<?php

namespace App\Models;

use Database\Factories\AssignmentAllowedJobListingsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentAllowedJobListings extends Model
{
    /** @use HasFactory<AssignmentAllowedJobListingsFactory> */
    use HasFactory;

    public $timestamps = false;
}
