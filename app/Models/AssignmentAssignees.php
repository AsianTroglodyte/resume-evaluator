<?php

namespace App\Models;

use Database\Factories\AssignmentAssigneesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentAssignees extends Model
{
    /** @use HasFactory<AssignmentAssigneesFactory> */
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'user_id',
    ];
}
