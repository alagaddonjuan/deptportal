<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // <--- IMPORT THIS TRAIT
use App\Models\Grade;
use App\Models\Enrollment;
use App\Models\Assessment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
// No need to import SubmissionGraded notification here directly unless dispatching from here

class GradeController extends Controller
{
    use AuthorizesRequests; // <--- USE THIS TRAIT

    public function index(Request $request)
    {
        $this->authorize('viewAny', Grade::class); // Policy for listing all grades

        $validator = Validator::make($request->all(), [
            'enrollment_id' => 'nullable|integer|exists:enrollments,id',
            'assessment_id' => 'nullable|integer|exists:assessments,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $grades = Grade::with(['enrollment.student:id,name,email', 'assessment:id,name,subject_id', 'assessment.subject:id,name,code'])
            ->when($request->query('enrollment_id'), function ($query, $enrollmentId) {
                return $query->where('enrollment_id', $enrollmentId);
            })
            ->when($request->query('assessment_id'), function ($query, $assessmentId) {
                return $query->where('assessment_id', $assessmentId);
            })
            ->latest()
            ->paginate(20);

        return response()->json($grades);
    }

    public function store(Request $request)
    {
        // Authorize using the 'create' method of GradePolicy
        // Pass Grade class and request data for context (assessment_id, enrollment_id)
        $this->authorize('create', [Grade::class, $request->all()]); // Line 52 is likely here or in index()

        $validator = Validator::make($request->all(), [
            'enrollment_id' => [
                'required','integer', Rule::exists('enrollments', 'id'),
                Rule::unique('grades')->where(function ($query) use ($request) {
                    return $query->where('assessment_id', $request->assessment_id);
                }),
            ],
            'assessment_id' => 'required|integer|exists:assessments,id',
            'marks_obtained' => 'required|numeric|min:0',
            'grade_letter' => 'nullable|string|max:5',
            'comments' => 'nullable|string',
            'grading_date' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $assessment = Assessment::find($request->assessment_id);
        if (!$assessment) { // Should be caught by exists rule, but defensive check
            return response()->json(['errors' => ['assessment_id' => ['Assessment not found.']]], 422);
        }
        if ($request->marks_obtained > $assessment->max_marks) {
            return response()->json(['errors' => ['marks_obtained' => ['Marks obtained cannot exceed max marks ('.$assessment->max_marks.').']]], 422);
        }
        
        $enrollment = Enrollment::with('course.subjects')->find($request->enrollment_id);
        if (!$enrollment) { // Should be caught by exists rule
            return response()->json(['errors' => ['enrollment_id' => ['Enrollment not found.']]], 422);
        }
        if (!$enrollment->course || !$enrollment->course->subjects->contains($assessment->subject_id)) {
            return response()->json(['errors' => ['assessment_id' => ['This assessment does not belong to a subject in the student\'s enrolled course.']]], 422);
        }

        $gradeData = $validator->validated();
        $gradeData['graded_by_user_id'] = Auth::id();
        if (empty($gradeData['grading_date'])) {
            $gradeData['grading_date'] = now()->toDateString();
        }

        $grade = Grade::create($gradeData);
        
        return response()->json([
            'message' => 'Grade recorded successfully.',
            'grade' => $grade->load(['enrollment.student:id,name', 'assessment:id,name'])
        ], 201);
    }

    public function show(Grade $grade)
    {
        $this->authorize('view', $grade); // Policy for viewing a specific grade
        return response()->json($grade->load(['enrollment.student:id,name,email', 'assessment.subject:id,name,code', 'gradedBy:id,name']));
    }

    public function update(Request $request, Grade $grade)
    {
        $this->authorize('update', $grade); // Policy for updating a grade

        $validator = Validator::make($request->all(), [
            'marks_obtained' => 'sometimes|required|numeric|min:0',
            'grade_letter' => 'nullable|string|max:5',
            'comments' => 'nullable|string',
            'grading_date' => 'sometimes|nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $assessment = $grade->assessment;
        if (!$assessment) { // Defensive check
             return response()->json(['message' => 'Associated assessment not found for this grade.'], 404);
        }
        if ($request->has('marks_obtained') && $request->marks_obtained > $assessment->max_marks) {
            return response()->json(['errors' => ['marks_obtained' => ['Marks obtained cannot exceed max marks ('.$assessment->max_marks.').']]], 422);
        }

        $updateData = $validator->validated();
        $updateData['graded_by_user_id'] = Auth::id(); 

        $grade->update($updateData);

        return response()->json([
            'message' => 'Grade updated successfully.',
            'grade' => $grade->fresh()->load(['enrollment.student:id,name', 'assessment:id,name'])
        ]);
    }

    public function destroy(Grade $grade)
    {
        $this->authorize('delete', $grade); // Policy for deleting a grade
        
        try {
            $grade->delete();
            return response()->json(['message' => 'Grade deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting grade record.'], 500);
        }
    }

    public function myGrades(Request $request) 
    {
        $student = Auth::user();
        
        $grades = Grade::whereHas('enrollment', function ($query) use ($student) {
                $query->where('user_id', $student->id);
            })
            ->with([
                'assessment:id,name,max_marks,subject_id', 
                'assessment.subject:id,name,code', 
                'enrollment.course:id,name,code'
            ])
            ->latest('grading_date')
            ->paginate(15);

        return response()->json($grades);
    }
}