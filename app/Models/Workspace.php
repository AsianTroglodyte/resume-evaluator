<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Boolean;

class Workspace extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'user_id',
    ];

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function hasPendingEvaluation()
    {
        // return $this->evaluations() ?? true;
        //             ->latest()
        $latestEvaluation = $this->evaluations()
            ->latest()
            ->limit(1)
            ->get()[0];

        // dd($latestEvaluation);

        return $latestEvaluation->status === "completed";
    }
}
