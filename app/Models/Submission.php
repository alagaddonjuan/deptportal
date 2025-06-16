<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'student_id', // User ID of the student who submitted
        'enrollment_id',
        'submission_date',
        'file_path',
        'text_content',
        'status',
        'marks_awarded',
        'feedback',
        'graded_at',
        'graded_by_user_id', // User ID of the teacher who graded it
    ];

    protected $casts = [
        'submission_date' => 'datetime',
        'graded_at' => 'datetime',
        'marks_awarded' => 'decimal:2',
    ];

    /**
     * Get the assignment that this submission belongs to.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get the student (user) who made this submission.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the enrollment context for this submission (optional).
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Get the user (teacher) who graded this submission.
     */
    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by_user_id');
    }
}