<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Notifications\SubmissionGraded; // Ensure this is imported
use Illuminate\Support\Facades\Log; // For logging if needed

class SubmissionController extends Controller
{
    /**
     * Student stores a new submission for a given assignment.
     */
    public function storeForAssignment(Request $request, Assignment $assignment)
    {
        $student = Auth::user();

        $isEnrolled = Enrollment::where('user_id', $student->id)
            ->whereHas('course.subjects', function ($query) use ($assignment) {
                $query->where('subjects.id', $assignment->subject_id);
            })
            ->exists();

        if (!$isEnrolled) {
            return response()->json(['message' => 'You are not authorized to submit to this assignment or not enrolled in the relevant course.'], 403);
        }

        if ($assignment->due_date && now()->gt($assignment->due_date)) {
            // Logic for late submissions
        }

        $existingSubmission = Submission::where('assignment_id', $assignment->id)
                                        ->where('student_id', $student->id)
                                        ->first();
        if ($existingSubmission) {
            return response()->json(['message' => 'You have already submitted for this assignment.'], 409);
        }

        $validator = Validator::make($request->all(), [
            'submission_file' => 'required_without:text_content|file|mimes:pdf,doc,docx,txt,zip,jpg,png|max:20480',
            'text_content' => 'required_without:submission_file|string|max:65535',
        ], [
            'submission_file.required_without' => 'A file is required if no text content is provided.',
            'text_content.required_without' => 'Text content is required if no file is provided.',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'student_id' => $student->id,
            'assignment_id' => $assignment->id,
            'submission_date' => now(),
            'status' => ($assignment->due_date && now()->gt($assignment->due_date)) ? 'late' : 'submitted',
        ];
        
        $enrollment = Enrollment::where('user_id', $student->id)
            ->whereHas('course.subjects', function ($query) use ($assignment) {
                $query->where('subjects.id', $assignment->subject_id);
            })->first(); 
        if($enrollment) {
            $data['enrollment_id'] = $enrollment->id;
        }

        if ($request->hasFile('submission_file')) {
            $filePath = $request->file('submission_file')->store("submissions/assignment_{$assignment->id}/student_{$student->id}", 'public');
            $data['file_path'] = $filePath;
        }

        if ($request->filled('text_content')) {
            $data['text_content'] = $request->text_content;
        }

        $submission = Submission::create($data);

        return response()->json([
            'message' => 'Submission successful.',
            'submission' => $submission->load(['student:id,name', 'assignment:id,title'])
        ], 201);
    }

    /**
     * Display a listing of submissions for a specific assignment.
     * (For Teacher/Admin)
     */
    public function indexForAssignment(Request $request, Assignment $assignment)
    {
        $user = Auth::user();
        $assignment->load('subject'); // Ensure subject is loaded for teacher_id check

        if (!$user->isAdmin() && $assignment->teacher_id !== $user->id && (!$assignment->subject || $assignment->subject->teacher_id !== $user->id)) {
            return response()->json(['message' => 'Unauthorized to view submissions for this assignment.'], 403);
        }

        $submissions = Submission::where('assignment_id', $assignment->id)
            ->with('student:id,name,email')
            ->orderBy('submission_date', 'desc')
            ->paginate(15);

        return response()->json($submissions);
    }

    /**
     * Display a listing of the authenticated student's submissions.
     */
    public function mySubmissions(Request $request)
    {
        $student = Auth::user();
        $validator = Validator::make($request->all(), [
            'assignment_id' => 'nullable|integer|exists:assignments,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $submissions = Submission::where('student_id', $student->id)
            ->with('assignment:id,title,due_date,subject_id', 'assignment.subject:id,name,code')
            ->when($request->query('assignment_id'), function ($query, $assignmentId) {
                return $query->where('assignment_id', $assignmentId);
            })
            ->orderBy('submission_date', 'desc')
            ->paginate(15);

        return response()->json($submissions);
    }

    /**
     * Display the specified submission.
     * (Student who submitted, Teacher of assignment, Admin)
     */
    public function show(Submission $submission)
    {
        $user = Auth::user();
        // Eager load assignment and its subject for authorization checks and response
        $submission->loadMissing(['assignment.subject', 'student:id,name,email', 'gradedBy:id,name']);
        $assignment = $submission->assignment; 

        if ($user->isAdmin() || 
            $submission->student_id === $user->id || 
            ($assignment && $assignment->teacher_id === $user->id) || 
            ($assignment && $assignment->subject && $assignment->subject->teacher_id === $user->id) 
           ) {
            if ($submission->file_path) {
                $submission->download_url = Storage::disk('public')->url($submission->file_path);
            }
            return response()->json($submission);
        }

        return response()->json(['message' => 'Unauthorized to view this submission.'], 403);
    }

    /**
     * Allow downloading the submitted file.
     */
    public function downloadFile(Submission $submission)
    {
        $user = Auth::user();
        $submission->loadMissing('assignment.subject'); // Ensure relationships are loaded
        $assignment = $submission->assignment;

        if (!$user->isAdmin() && 
            $submission->student_id !== $user->id && 
            (!$assignment || $assignment->teacher_id !== $user->id) && 
            (!$assignment || !$assignment->subject || $assignment->subject->teacher_id !== $user->id)
           ) {
             return response()->json(['message' => 'Unauthorized to download this file.'], 403);
        }

        if (!$submission->file_path || !Storage::disk('public')->exists($submission->file_path)) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        return Storage::disk('public')->download($submission->file_path);
    }
    
    /**
     * Remove the specified submission from storage.
     * (Admin only for now, or teacher who owns assignment)
     */
    public function destroy(Submission $submission)
    {
        $user = Auth::user();
        $submission->loadMissing('assignment.subject'); // Ensure relationships are loaded
        $assignment = $submission->assignment;

         if (!$user->isAdmin() && 
             (!$assignment || $assignment->teacher_id !== $user->id) && 
             (!$assignment || !$assignment->subject || $assignment->subject->teacher_id !== $user->id)
            ) {
            return response()->json(['message' => 'Unauthorized to delete this submission.'], 403);
        }

        if ($submission->file_path) {
            Storage::disk('public')->delete($submission->file_path);
        }
        $submission->delete();

        return response()->json(['message' => 'Submission deleted successfully.'], 200);
    }

    /**
     * Teacher grades a submission.
     */
    public function gradeSubmission(Request $request, Submission $submission)
    {
        $user = Auth::user(); // This should be a teacher or admin
        
        // Eager load necessary relationships for authorization and logic
        $submission->loadMissing(['assignment.subject', 'student']);
        $assignment = $submission->assignment;

        if (!$assignment) { // Should not happen if DB constraints are fine
            return response()->json(['message' => 'Associated assignment not found.'], 404);
        }
        if (!$assignment->subject) { // Should not happen if DB constraints are fine
             return response()->json(['message' => 'Associated subject for assignment not found.'], 404);
        }


        // Authorization: Admin or teacher of the assignment's subject or creator of assignment
        if (!$user->isAdmin() && $assignment->teacher_id !== $user->id && $assignment->subject->teacher_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized to grade this submission.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'marks_awarded' => 'required|numeric|min:0',
            'feedback' => 'nullable|string|max:65535',
            'status' => 'sometimes|string|in:graded,submitted,late,resubmitted',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // $assignment->max_marks should be directly accessible as it's an attribute
        if ($request->marks_awarded > $assignment->max_marks) {
            return response()->json(['errors' => ['marks_awarded' => ['Marks awarded cannot exceed max marks ('.$assignment->max_marks.') for this assignment.']]], 422);
        }

        $submission->marks_awarded = $request->marks_awarded;
        $submission->feedback = $request->input('feedback', $submission->feedback);
        $submission->status = $request->input('status', 'graded'); 
        $submission->graded_at = now();
        $submission->graded_by_user_id = $user->id;
        $submission->save();

        // Send Notification to the Student
        $studentToNotify = $submission->student; 
        if ($studentToNotify) {
            // Ensure the submission model has the necessary relationships loaded for the notification
            // assignment.subject was loaded at the start of the method.
            // assignment:id,title,max_marks for the notification
            $submission->loadMissing(['assignment:id,title,max_marks']); 
            $studentToNotify->notify(new SubmissionGraded($submission));
            Log::info('SubmissionGraded notification dispatched for submission ID: ' . $submission->id . ' to student ID: ' . $studentToNotify->id);
        } else {
            Log::warning('Student not found for submission ID: ' . $submission->id . ' - cannot send SubmissionGraded notification.');
        }

        return response()->json([
            'message' => 'Submission graded successfully.',
            'submission' => $submission->fresh()->load(['student:id,name', 'assignment:id,title', 'gradedBy:id,name'])
        ]);
    }
}