<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'subject_id',
        'session_date',
        'session_time',
        'status',
        'remarks',
        'marked_by_user_id',
    ];

    protected $casts = [
        'session_date' => 'date',
        // 'session_time' => 'datetime:H:i', // Or just 'time' if your DB supports it cleanly.
                                          // Storing as string HH:MM might be simpler if time type causes issues.
    ];

    /**
     * Get the enrollment record this attendance belongs to.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Get the subject this attendance is for.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the user (teacher) who marked this attendance.
     */
    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by_user_id');
    }

     /**
     * Get the class schedule entry this attendance record might correspond to.
     * This is an approximation as attendance doesn't directly link to a class_schedules.id.
     */
    public function classSchedule(): BelongsTo // Or a custom relationship
    {
        // This relationship is a bit indirect because attendances aren't directly linked
        // to a class_schedules.id by default. It's more of a conceptual link.
        // For the policy, we might need to load subject and check its teacher_id.
        // A more direct approach is to pass the relevant ClassSchedule to the policy if possible from controller.
        // Let's assume for the policy, we rely on subject's teacher and marked_by_user_id for now.
        // If you add class_schedule_id to attendances table, this becomes a simple BelongsTo.
        // For now, the policy will check based on subject and marked_by.
        // This relationship might not be directly usable as a standard BelongsTo without a class_schedule_id FK.
        // So, the policy logic for $attendance->classSchedule->teacher_id will rely on the controller ensuring
        // that the attendance record is contextually tied to a class schedule.
        // A better way would be to have the policy check:
        // $attendance->subject->teacher_id === $user->id (if subject is loaded)
        // For now, the policy uses $attendance->marked_by_user_id or expects subject teacher check.
        return $this->belongsTo(ClassSchedule::class, 'subject_id', 'subject_id') // This is not a real FK
             ->where('day_of_week', function($query) {
                 // This is tricky: need to convert attendance->session_date to day_of_week
                 // and match session_time. It's better to handle this logic in the policy directly
                 // by looking up the schedule based on subject, date, and time from attendance.
             });
        // Let's simplify policy: relies on marked_by or controller passing subject/class context.
    }
}