<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSchedule extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'class_schedules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subject_id',
        'teacher_id',
        'day_of_week',
        'start_time',
        'end_time',
        'location',
        'academic_year_term',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'day_of_week' => 'integer',
        // 'start_time' => 'datetime:H:i', // Laravel casts TIME columns to Carbon instances by default.
        // 'end_time' => 'datetime:H:i',   // Explicit casting can format, but often not needed for retrieval.
                                        // For storage, ensure format is H:i or H:i:s.
    ];

    /**
     * Get the subject for this class schedule.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the teacher assigned to this class schedule.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}