<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\Subject; // We'll need this for the create method
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization; // Or Response for Laravel 10+

class AssessmentPolicy
{
    // For Laravel 10+ style, you might use: use Illuminate\Auth\Access\Response;
    // And methods can return Response::allow() or Response::deny().
    // For broader compatibility, boolean return is fine and will be converted.
    // use HandlesAuthorization; // This trait is often included by default.

    /**
     * Determine whether the user can view any models.
     * All authenticated users can view lists of assessments.
     */
    public function viewAny(User $user): bool
    {
        return true; // Any authenticated user can list assessments
    }

    /**
     * Determine whether the user can view the model.
     * All authenticated users can view a specific assessment.
     */
    public function view(User $user, Assessment $assessment): bool
    {
        return true; // Any authenticated user can view a specific assessment
                     // We can refine later if students should only see assessments for their enrolled subjects.
    }

    /**
     * Determine whether the user can create models.
     * User can create if they are an admin OR if they are the teacher assigned to the subject.
     * Note: The $subject model is passed to this policy method from the controller.
     */
    public function create(User $user, Subject $subject): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        // Check if the user is the teacher assigned to the subject
        return $subject->teacher_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     * User can update if they are an admin OR if they are the teacher of the subject this assessment belongs to.
     */
    public function update(User $user, Assessment $assessment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        // Check if the user is the teacher assigned to the assessment's subject
        return $assessment->subject->teacher_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     * User can delete if they are an admin OR if they are the teacher of the subject this assessment belongs to.
     */
    public function delete(User $user, Assessment $assessment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        // Check if the user is the teacher assigned to the assessment's subject
        return $assessment->subject->teacher_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model. (If using soft deletes)
     */
    // public function restore(User $user, Assessment $assessment): bool
    // {
    //     // Define logic if you use soft deletes
    // }

    /**
     * Determine whether the user can permanently delete the model. (If using soft deletes)
     */
    // public function forceDelete(User $user, Assessment $assessment): bool
    // {
    //     // Define logic if you use soft deletes
    // }
}