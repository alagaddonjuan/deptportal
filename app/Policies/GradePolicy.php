<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;
use App\Models\Assessment; // Needed for create
use App\Models\Enrollment; // Needed for create
use Illuminate\Auth\Access\HandlesAuthorization;

class GradePolicy
{
    // use HandlesAuthorization; // Often included by default

    /**
     * Determine whether the user can view any models.
     * Only admins can list ALL grades. Teachers/students have specific views.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     * Admin, the student whose grade it is, or a teacher of the subject.
     */
    public function view(User $user, Grade $grade): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Check if the user is the student to whom the grade belongs
        if ($grade->enrollment && $grade->enrollment->user_id === $user->id) {
            return true;
        }

        // Check if the user is a teacher for the subject of this grade's assessment
        if ($user->hasRole('teacher') && $grade->assessment && $grade->assessment->subject) {
            return $grade->assessment->subject->teacher_id === $user->id;
        }
        
        // Or if the teacher is the one who graded it
        if ($user->hasRole('teacher') && $grade->graded_by_user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     * Admin, or a teacher for an assessment in a subject they teach.
     * We pass $requestData to check assessment_id and enrollment_id.
     */
    public function create(User $user, array $requestData): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->hasRole('teacher')) {
            $assessment = Assessment::find($requestData['assessment_id'] ?? null);
            $enrollment = Enrollment::find($requestData['enrollment_id'] ?? null);

            if ($assessment && $enrollment && $assessment->subject) {
                // Teacher must teach the subject of the assessment
                // And the enrollment's course must contain the subject of the assessment (already validated in controller)
                return $assessment->subject->teacher_id === $user->id;
            }
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     * Admin, or teacher of the subject of the grade's assessment.
     */
    public function update(User $user, Grade $grade): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Teacher can update if they teach the subject of the assessment for this grade
        // or if they were the one who originally graded it.
        if ($user->hasRole('teacher') && $grade->assessment && $grade->assessment->subject) {
            if ($grade->assessment->subject->teacher_id === $user->id || $grade->graded_by_user_id === $user->id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * For now, only admins can delete grades.
     */
    public function delete(User $user, Grade $grade): bool
    {
        return $user->isAdmin();
    }
}