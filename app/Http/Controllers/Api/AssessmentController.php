<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Subject; // To validate subject_id
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth; // To get authenticated user for authorization checks later

class AssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     * Can be filtered by subject_id. e.g., GET /api/assessments?subject_id=1
     * Accessible by authenticated users.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'nullable|integer|exists:subjects,id',
            'type' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $assessments = Assessment::with('subject:id,name,code') // Eager load subject details
            ->when($request->query('subject_id'), function ($query, $subjectId) {
                return $query->where('subject_id', $subjectId);
            })
            ->when($request->query('type'), function ($query, $type) {
                return $query->where('type', $type);
            })
            ->latest()
            ->paginate(15);

        return response()->json($assessments);
    }

    /**
     * Store a newly created resource in storage.
     * (Admin only for now)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|integer|exists:subjects,id',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50|in:exam,quiz,homework,project,participation,other',
            'max_marks' => 'required|numeric|min:0',
            'weightage' => 'nullable|numeric|min:0|max:1', // e.g., 0.25 for 25%
            'assessment_date' => 'nullable|date_format:Y-m-d',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Authorization check (already handled by isAdmin middleware for now)
        // If we want teachers to create assessments for their subjects:
        // $subject = Subject::find($request->subject_id);
        // if (Auth::user()->cannot('createAssessmentForSubject', $subject)) {
        //     return response()->json(['message' => 'Unauthorized to create assessments for this subject.'], 403);
        // }

        $assessment = Assessment::create($validator->validated());

        return response()->json([
            'message' => 'Assessment created successfully.',
            'assessment' => $assessment->load('subject:id,name,code')
        ], 201);
    }

    /**
     * Display the specified resource.
     * Accessible by authenticated users.
     */
    public function show(Assessment $assessment)
    {
        return response()->json($assessment->load('subject:id,name,code'));
    }

    /**
     * Update the specified resource in storage.
     * (Admin only for now)
     */
    public function update(Request $request, Assessment $assessment)
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'sometimes|required|integer|exists:subjects,id', // Usually not changed, but can be allowed
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|nullable|string|max:50|in:exam,quiz,homework,project,participation,other',
            'max_marks' => 'sometimes|required|numeric|min:0',
            'weightage' => 'sometimes|nullable|numeric|min:0|max:1',
            'assessment_date' => 'sometimes|nullable|date_format:Y-m-d',
            'description' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Authorization check (already handled by isAdmin middleware for now)
        // if (Auth::user()->cannot('update', $assessment)) { // Assuming an AssessmentPolicy or Gate
        //     return response()->json(['message' => 'Unauthorized to update this assessment.'], 403);
        // }
        
        // If subject_id is being changed, re-verify authorization for the new subject if necessary
        if ($request->has('subject_id') && $request->subject_id != $assessment->subject_id) {
            $newSubject = Subject::find($request->subject_id);
            // Add authorization check for newSubject if needed
        }

        $assessment->update($validator->validated());

        return response()->json([
            'message' => 'Assessment updated successfully.',
            'assessment' => $assessment->fresh()->load('subject:id,name,code')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * (Admin only for now)
     */
    public function destroy(Assessment $assessment)
    {
        // Authorization check (already handled by isAdmin middleware for now)
        // if (Auth::user()->cannot('delete', $assessment)) {
        //     return response()->json(['message' => 'Unauthorized to delete this assessment.'], 403);
        // }

        // Consider what happens to grades if an assessment is deleted.
        // If onDelete('cascade') is set in the grades migration for assessment_id, grades will be auto-deleted.
        try {
            $assessment->delete();
            return response()->json(['message' => 'Assessment deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting assessment. It might be associated with grades.'], 500);
        }
    }
}