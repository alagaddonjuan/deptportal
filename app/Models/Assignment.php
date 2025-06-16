<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'teacher_id', // User ID of the teacher who created it
        'title',
        'description',
        'due_date',
        'max_marks',
        'file_path',
        'status',
        'posted_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'posted_at' => 'datetime',
        'max_marks' => 'decimal:2',
    ];

    /**
     * Get the subject that this assignment belongs to.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the teacher (user) who created this assignment.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get all submissions for this assignment.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}