<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of the resource for admins.
     * Allows filtering by user_id, course_id, status, academic_year.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|integer|exists:users,id',
            'course_id' => 'nullable|integer|exists:courses,id',
            'status' => 'nullable|string',
            'academic_year' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $enrollments = Enrollment::with(['student:id,name,email', 'course:id,name,code'])
            ->when($request->query('user_id'), function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->when($request->query('course_id'), function ($query, $courseId) {
                return $query->where('course_id', $courseId);
            })
            ->when($request->query('status'), function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->query('academic_year'), function ($query, $academicYear) {
                return $query->where('academic_year', $academicYear);
            })
            ->latest()
            ->paginate(20);

        return response()->json($enrollments);
    }

    /**
     * Store a newly created enrollment in storage (Admin action).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    // Optionally ensure the user has the 'student' role
                    // $query->whereExists(function ($subQuery) {
                    //     $subQuery->select(DB::raw(1))
                    //              ->from('roles')
                    //              ->join('users as u_join', 'u_join.role_id', '=', 'roles.id')
                    //              ->whereColumn('u_join.id', 'users.id') // This might need adjustment to correctly reference the outer users.id
                    //              ->where('roles.slug', 'student');
                    // });
                    // Simpler check after fetching user:
                }),
            ],
            'course_id' => 'required|integer|exists:courses,id',
            'enrollment_date' => 'nullable|date_format:Y-m-d',
            'status' => 'sometimes|string|in:enrolled,completed,withdrawn,pending',
            'academic_year' => 'nullable|string|max:20',
            'semester_term' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ], [
            'user_id.exists' => 'The selected student does not exist or is not a valid student.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verify the user is a student (if not covered adequately by Rule::exists constraint modification)
        $student = User::find($request->user_id);
        if (!$student || !$student->hasRole('student')) { // Assumes hasRole() method on User model
            return response()->json(['errors' => ['user_id' => ['The selected user is not a student.']]], 422);
        }
        
        // Check for duplicate enrollment (based on unique constraint in migration)
        $existingEnrollment = Enrollment::where('user_id', $request->user_id)
                                        ->where('course_id', $request->course_id)
                                        ->where('academic_year', $request->input('academic_year', null)) // Match null if academic_year is not provided
                                        ->first();

        if ($existingEnrollment) {
            return response()->json(['message' => 'Student is already enrolled in this course for the specified academic year.'], 409); // 409 Conflict
        }

        $enrollmentData = $validator->validated();
        if (empty($enrollmentData['enrollment_date'])) {
            $enrollmentData['enrollment_date'] = now()->toDateString();
        }

        $enrollment = Enrollment::create($enrollmentData);

        return response()->json([
            'message' => 'Student enrolled successfully.',
            'enrollment' => $enrollment->load(['student:id,name', 'course:id,name'])
        ], 201);
    }

    /**
     * Display the specified enrollment (Admin action).
     *
     * @param  \App\Models\Enrollment  $enrollment
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Enrollment $enrollment)
    {
        return response()->json($enrollment->load(['student:id,name,email', 'course:id,name,code']));
    }

    /**
     * Update the specified enrollment in storage (Admin action).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Enrollment  $enrollment
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Enrollment $enrollment)
    {
        $validator = Validator::make($request->all(), [
            // user_id and course_id are generally not updatable for an existing enrollment record.
            // If they need to change, it's usually a new enrollment.
            'enrollment_date' => 'sometimes|required|date_format:Y-m-d',
            'status' => 'sometimes|required|string|in:enrolled,completed,withdrawn,pending',
            'academic_year' => 'sometimes|nullable|string|max:20',
            'semester_term' => 'sometimes|nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Check for duplicate if academic_year is changing and relevant fields form a unique key
        if ($request->has('academic_year') && $request->academic_year != $enrollment->academic_year) {
            $existingEnrollment = Enrollment::where('user_id', $enrollment->user_id)
                                            ->where('course_id', $enrollment->course_id)
                                            ->where('academic_year', $request->academic_year)
                                            ->where('id', '!=', $enrollment->id) // Exclude current record
                                            ->first();
            if ($existingEnrollment) {
                 return response()->json(['message' => 'This update would create a duplicate enrollment for the student in this course for the specified academic year.'], 409);
            }
        }

        $enrollment->update($validator->validated());

        return response()->json([
            'message' => 'Enrollment updated successfully.',
            'enrollment' => $enrollment->fresh()->load(['student:id,name', 'course:id,name'])
        ]);
    }

    /**
     * Remove the specified enrollment from storage (Admin action).
     *
     * @param  \App\Models\Enrollment  $enrollment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Enrollment $enrollment)
    {
        try {
            $enrollment->delete();
            return response()->json(['message' => 'Enrollment deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting enrollment record.'], 500);
        }
    }

    /**
     * Display a listing of the authenticated student's enrollments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function myEnrollments(Request $request)
    {
        $student = Auth::user(); // Get currently authenticated user

        if (!$student->hasRole('student')) {
             // This check might be redundant if only students are expected to hit this
             // Or if other roles might have "enrollments" in a different context.
             // For now, let's assume it's for students.
            // return response()->json(['message' => 'This action is for students only.'], 403);
        }

        $enrollments = Enrollment::where('user_id', $student->id)
            ->with(['course:id,name,code,level']) // Eager load course details
            // ->where('status', 'enrolled') // Optionally filter by status
            ->latest('enrollment_date')
            ->paginate(10);

        return response()->json($enrollments);
    }

    /**
     * Get the student roster (enrollments) for a specific subject's course.
     * Accessible by the teacher of the subject or an admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Subject  $subject
     * @return \Illuminate\Http\JsonResponse
     */
    public function rosterForSubject(Request $request, Subject $subject)
    {
        $user = Auth::user();

        // Authorization: Check if the user is an admin or the teacher assigned to this subject.
        if (!$user->isAdmin() && $subject->teacher_id !== $user->id) {
            return response()->json(['message' => 'You are not authorized to view the roster for this subject.'], 403);
        }

        // Fetch all active enrollments for the course that this subject belongs to.
        $enrollments = Enrollment::where('course_id', $subject->course_id)
                                   ->where('status', 'enrolled')
                                   ->with('student:id,name,email') // Eager load student details for the roster
                                   ->get();

        // We are returning a simple array here, not a paginated response,
        // as a class roster is usually viewed all at once.
        return response()->json($enrollments);
    }
}