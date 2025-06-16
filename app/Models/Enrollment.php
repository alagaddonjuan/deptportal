<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // Already likely there
use App\Models\Assessment; // Add this
use App\Models\Attendance; // Add this
use App\Models\Submission; // Add this import

class Enrollment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'enrollments'; // Explicitly define if not conventional pluralization

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'enrollment_date',
        'status',
        'academic_year',
        'semester_term',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enrollment_date' => 'date',
        'semester_term' => 'integer',
    ];

    /**
     * Get the student (user) associated with this enrollment.
     */
    public function student(): BelongsTo // Using 'student' as a more descriptive name
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the course associated with this enrollment.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get all grades associated with this enrollment.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Get all attendance records associated with this enrollment.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get all submissions associated with this enrollment.
     * (This assumes submissions have an enrollment_id)
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

}