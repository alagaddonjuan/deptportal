<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'assessment_id',
        'marks_obtained',
        'grade_letter',
        'comments',
        'graded_by_user_id',
        'grading_date',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'grading_date' => 'date',
    ];

    /**
     * Get the enrollment record this grade belongs to.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Get the assessment this grade is for.
     */
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    /**
     * Get the user (teacher) who graded this.
     */
    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by_user_id');
    }
}