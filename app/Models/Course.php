<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User; // Make sure User model is imported
use App\Models\Enrollment; // Make sure Enrollment model is imported

class Course extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'level',
        'duration_years',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'duration_years' => 'integer',
    ];

    /**
     * Get the subjects for the course.
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the students (users) enrolled in this course.
     * This uses the enrollments table as the pivot table.
     */
    public function enrolledStudents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments', 'course_id', 'user_id')
                    ->withPivot('enrollment_date', 'status', 'academic_year', 'semester_term', 'notes')
                    ->withTimestamps();
    }
}