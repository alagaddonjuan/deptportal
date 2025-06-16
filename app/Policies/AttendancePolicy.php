<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Subject; // For create policy logic
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class AttendancePolicy
{
    // use HandlesAuthorization; // Typically included by default via base Policy class

    public function viewAny(User $user): bool
    {
        Log::info("[Policy][viewAny] Checking for User ID: {$user->id}");
        if ($user->isAdmin()) {
            Log::info("[Policy][viewAny] User is admin. Allowing.");
            return true;
        }
        Log::info("[Policy][viewAny] User is not admin. Denying.");
        return false;
    }

    public function view(User $user, Attendance $attendance): bool
    {
        Log::info("--- [Policy][view] START --- User: {$user->id} (Role: {$user->role->slug}), Attendance ID: {$attendance->id}");

        if ($user->isAdmin()) {
            Log::info("[Policy][view] User is admin. ALLOWED.");
            return true;
        }

        // Ensure enrollment and its student are loaded for the check
        $attendance->loadMissing('enrollment.student', 'subject', 'markedBy'); // Load all needed relationships

        if ($attendance->enrollment && $attendance->enrollment->student && $attendance->enrollment->student->id === $user->id) {
            Log::info("[Policy][view] User is the student for this attendance. ALLOWED.");
            return true;
        }
        Log::info("[Policy][view] User is NOT the student (Student ID on enrollment: " . ($attendance->enrollment->student_id ?? 'N/A') . ").");


        if ($user->hasRole('teacher')) {
            Log::info("[Policy][view] User is a teacher. Checking teacher conditions...");
            Log::info("[Policy][view] Attendance marked by: " . ($attendance->marked_by_user_id ?? 'N/A') . ". Current teacher ID: {$user->id}");
            if ($attendance->marked_by_user_id === $user->id) {
                Log::info("[Policy][view] Teacher marked this attendance. ALLOWED.");
                return true;
            }

            if ($attendance->subject) {
                Log::info("[Policy][view] Attendance subject ID: {$attendance->subject->id}. Subject's assigned teacher ID: " . ($attendance->subject->teacher_id ?? 'N/A'));
                if ($attendance->subject->teacher_id === $user->id) {
                    Log::info("[Policy][view] User is the teacher of the subject for this attendance. ALLOWED.");
                    return true;
                }
            } else {
                Log::info("[Policy][view] Attendance record has no associated subject or subject could not be loaded.");
            }
        } else {
            Log::info("[Policy][view] User is not a teacher.");
        }

        Log::info("--- [Policy][view] END --- No conditions met. DENIED.");
        return false;
    }

    public function create(User $user, array $requestData): bool
    {
        Log::info("[Policy][create] Checking for User ID: {$user->id} (Role: {$user->role->slug}), Data: " . json_encode($requestData));
        if ($user->isAdmin()) {
            Log::info("[Policy][create] User is admin. Allowing.");
            return true;
        }
        if ($user->hasRole('teacher')) {
            $subjectId = $requestData['subject_id'] ?? null;
            if ($subjectId) {
                $subject = Subject::find($subjectId);
                if ($subject && $subject->teacher_id === $user->id) {
                    Log::info("[Policy][create] User is teacher of subject ID: {$subjectId}. Allowing.");
                    return true;
                }
                Log::info("[Policy][create] User is teacher, but not for subject ID: {$subjectId}. Subject's teacher_id: " . ($subject->teacher_id ?? 'None'));
            } else {
                Log::info("[Policy][create] User is teacher, but no subject_id in requestData.");
            }
        }
        Log::info("[Policy][create] No conditions met. Denying.");
        return false;
    }

    public function update(User $user, Attendance $attendance): bool
    {
        Log::info("[Policy][update] Checking for User ID: {$user->id} (Role: {$user->role->slug}), Attendance ID: {$attendance->id}");
        if ($user->isAdmin()) {
            Log::info("[Policy][update] User is admin. Allowing.");
            return true;
        }
        if ($user->hasRole('teacher')) {
            $attendance->loadMissing('subject');
             if ($attendance->marked_by_user_id === $user->id) {
                Log::info("[Policy][update] User marked this attendance. Allowing.");
                return true;
            }
            if ($attendance->subject && $attendance->subject->teacher_id === $user->id) {
                Log::info("[Policy][update] User is teacher of subject. Allowing.");
                return true;
            }
        }
        Log::info("[Policy][update] No conditions met. Denying.");
        return false;
    }

    public function delete(User $user, Attendance $attendance): bool
    {
        Log::info("[Policy][delete] Checking for User ID: {$user->id} (Role: {$user->role->slug}), Attendance ID: {$attendance->id}");
        if ($user->isAdmin()) {
            Log::info("[Policy][delete] User is admin. Allowing.");
            return true;
        }
        Log::info("[Policy][delete] User is not admin. Denying.");
        return false;
    }
}