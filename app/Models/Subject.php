<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // Add this if not present
use App\Models\Grade; // Add this
use App\Models\Attendance; // Add this
use App\Models\ClassSchedule; // Add this if not already present (e.g. from Assessment relations)
use App\Models\Assignment;

class Subject extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'teacher_id',
        'name',
        'code',
        'description',
        'credits',
        'type',
        'semester_term',
        'status',
    ];

    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'credits' => 'decimal:2', // Assuming credits can be like 3.50
        'semester_term' => 'integer',
    ];

    /**
     * Get the course that the subject belongs to.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the teacher assigned to the subject.
     */
    public function teacher(): BelongsTo
    {
        // Assuming 'teacher_id' in the 'subjects' table references the 'id' in the 'users' table
        // and these users have a 'teacher' role.
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get all assessments for the subject.
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    /**
     * Get all attendance records for this subject.
     * (Note: This gets ALL attendance for this subject across all students/enrollments.
     * You'd typically filter by enrollment to get a specific student's attendance for this subject).
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function classSchedules(): HasMany
{
    return $this->hasMany(ClassSchedule::class);
}

/**
     * Get all assignments for the subject.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

}
