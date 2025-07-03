?php

namespace App\Observers;

use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Support\Facades\Notification;

class AssignmentObserver
{
    /**
     * Handle the Assignment "created" event.
     */
    public function created(Assignment $assignment): void
    {
        // Find the course this assignment's subject belongs to
        $courseId = $assignment->subject->course_id;

        // Find all students enrolled in that course
        $studentIds = Enrollment::where('course_id', $courseId)
            ->where('status', 'enrolled')
            ->pluck('student_user_id');

        $students = User::whereIn('id', $studentIds)->get();

        // Send a notification to each student
        if ($students->isNotEmpty()) {
            Notification::send($students, new NewAssignmentNotification($assignment));
        }
    }
}
