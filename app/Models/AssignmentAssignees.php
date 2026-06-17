<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentAssignees extends Model
{
    /** @use HasFactory<\Database\Factories\AssignmentAssigneesFactory> */
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'user_id'
    ];

    
}
