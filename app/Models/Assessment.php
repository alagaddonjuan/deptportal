<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'name',
        'type',
        'max_marks',
        'weightage',
        'assessment_date',
        'description',
    ];

    protected $casts = [
        'max_marks' => 'decimal:2',
        'weightage' => 'decimal:4',
        'assessment_date' => 'date',
    ];

    /**
     * Get the subject that this assessment belongs to.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the grades associated with this assessment.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}