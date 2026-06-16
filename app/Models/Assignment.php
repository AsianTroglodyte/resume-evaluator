<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'group_id',
        'created_by_user_id',
        'title',
        'description',
        'status',
        'due_at',
        'assignment_scope',
        'job_listing_rule',
        'allow_resubmission',
    ];
}
