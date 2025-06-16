<?php
\Illuminate\Support\Facades\Log::info('--- API ROUTE FILE LOADED ---'); // Test log
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\AssessmentController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\ClassScheduleController;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\SubmissionController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Admin\SchoolConfigurationController;
use App\Http\Controllers\Api\Admin\GuardianStudentLinkController;

/* API Routes */

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// Protected routes (require authentication via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user()->load(['role', 'profile']);
    });

    // User Profile & Self-Management ("My ..." routes)
    Route::get('/profile', [UserController::class, 'showProfile'])->name('profile.show');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::post('/password/change', [UserController::class, 'changePassword'])->name('password.change');
    Route::get('/my-enrollments', [EnrollmentController::class, 'myEnrollments'])->name('enrollments.mine');
    Route::get('/my-grades', [GradeController::class, 'myGrades'])->name('grades.mine');
    Route::get('/my-attendance', [AttendanceController::class, 'myAttendance'])->name('attendance.mine');
    Route::get('/my-timetable', [ClassScheduleController::class, 'myTimetable'])->name('timetable.mine');
    Route::get('/my-submissions', [SubmissionController::class, 'mySubmissions'])->name('submissions.mine');
    Route::get('/my-notifications', [NotificationController::class, 'index'])->name('notifications.mine');
    Route::post('/my-notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
    Route::patch('/my-notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markRead');
    Route::get('/my-children', [UserController::class, 'myChildren'])->name('my-children.index');

    // Teacher Specific Routes
    Route::get('/teacher-timetable', [ClassScheduleController::class, 'teacherTimetable'])->name('timetable.teacher');
    Route::get('/teacher/subjects/{subject}/roster', [EnrollmentController::class, 'rosterForSubject'])->name('teacher.subjects.roster');

    // General Resource Management (Authorization handled by Policies where applicable)
    // Course Management
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::post('/courses', [CourseController::class, 'store'])->middleware('isAdmin')->name('courses.store'); // Still admin only for course CUD
    Route::put('/courses/{course}', [CourseController::class, 'update'])->middleware('isAdmin')->name('courses.update');
    Route::patch('/courses/{course}', [CourseController::class, 'update'])->middleware('isAdmin');
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->middleware('isAdmin')->name('courses.destroy');

    // Subject Management
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::get('/subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
    Route::post('/subjects', [SubjectController::class, 'store'])->middleware('isAdmin')->name('subjects.store'); // Still admin only for subject CUD
    Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->middleware('isAdmin')->name('subjects.update');
    Route::patch('/subjects/{subject}', [SubjectController::class, 'update'])->middleware('isAdmin');
    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->middleware('isAdmin')->name('subjects.destroy');
    
    // Assessment Management (Policy handles auth for CUD)
    Route::get('/assessments', [AssessmentController::class, 'index'])->name('assessments.index');
    Route::get('/assessments/{assessment}', [AssessmentController::class, 'show'])->name('assessments.show');
    Route::post('/assessments', [AssessmentController::class, 'store'])->name('assessments.store.policy');
    Route::put('/assessments/{assessment}', [AssessmentController::class, 'update'])->name('assessments.update.policy');
    Route::patch('/assessments/{assessment}', [AssessmentController::class, 'update']);
    Route::delete('/assessments/{assessment}', [AssessmentController::class, 'destroy'])->name('assessments.destroy.policy');

    // General Grade Management (Policy handles auth for CUD)
    Route::get('/grades', [GradeController::class, 'index'])->name('grades.index.policy'); // Admin lists all (via policy)
    Route::post('/grades', [GradeController::class, 'store'])->name('grades.store.policy'); // Admin/Teacher creates (via policy)
    Route::get('/grades/{grade}', [GradeController::class, 'show'])->name('grades.show.policy'); // Admin/Teacher/Student views (via policy)
    Route::put('/grades/{grade}', [GradeController::class, 'update'])->name('grades.update.policy'); // Admin/Teacher updates (via policy)
    Route::delete('/grades/{grade}', [GradeController::class, 'destroy'])->name('grades.destroy.policy'); // Admin deletes (via policy)

    // Attendance Management (Policy handles auth for CUD)  <<< MOVED HERE
    Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index.policy');
    Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendances.store.policy');
    Route::get('/attendances/{attendance}', [AttendanceController::class, 'show'])->name('attendances.show.policy');
    Route::put('/attendances/{attendance}', [AttendanceController::class, 'update'])->name('attendances.update.policy');
    Route::delete('/attendances/{attendance}', [AttendanceController::class, 'destroy'])->name('attendances.destroy.policy');

    // Assignment Management (Authorization within controller for CUD)
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::post('/assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update'); // Using POST for file updates
    Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');
    Route::get('/assignments/{assignment}/download', [AssignmentController::class, 'downloadFile'])->name('assignments.download');

    // Submissions Routes
    Route::post('/assignments/{assignment}/submissions', [SubmissionController::class, 'storeForAssignment'])->name('submissions.storeForAssignment');
    Route::get('/assignments/{assignment}/submissions', [SubmissionController::class, 'indexForAssignment'])->name('submissions.indexForAssignment');
    Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
    Route::put('/submissions/{submission}/grade', [SubmissionController::class, 'gradeSubmission'])->name('submissions.grade');
    Route::get('/submissions/{submission}/download', [SubmissionController::class, 'downloadFile'])->name('submissions.download');
    Route::delete('/submissions/{submission}', [SubmissionController::class, 'destroy'])->middleware('isAdmin')->name('submissions.destroy'); // Kept admin only for now

    // Announcements - Viewing for authenticated users
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index.public');
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'showPublic'])->name('announcements.show.public');


    // Admin Only Specific Routes (Overall Management)
    Route::prefix('admin')->middleware('isAdmin')->name('admin.')->group(function () {
        // User Management (already here)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        
        // Enrollment Management (already here)
        Route::get('/enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
        // ... other admin enrollment routes ...
        Route::post('/enrollments', [EnrollmentController::class, 'store'])->name('enrollments.store');
        Route::get('/enrollments/{enrollment}', [EnrollmentController::class, 'show'])->name('enrollments.show');
        Route::put('/enrollments/{enrollment}', [EnrollmentController::class, 'update'])->name('enrollments.update');
        Route::delete('/enrollments/{enrollment}', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy');
        
        // Class Schedule Management (Admin only CUD was already here)
        Route::get('/class-schedules', [ClassScheduleController::class, 'index'])->name('class-schedules.index');
        // ... other admin class schedule routes ...
        Route::post('/class-schedules', [ClassScheduleController::class, 'store'])->name('class-schedules.store');
        Route::get('/class-schedules/{class_schedule}', [ClassScheduleController::class, 'show'])->name('class-schedules.show');
        Route::put('/class-schedules/{class_schedule}', [ClassScheduleController::class, 'update'])->name('class-schedules.update');
        Route::delete('/class-schedules/{class_schedule}', [ClassScheduleController::class, 'destroy'])->name('class-schedules.destroy');

        // Announcement Management (Admin CUD was already here)
        Route::get('/announcements', [AnnouncementController::class, 'adminIndex'])->name('announcements.index.admin');
        // ... other admin announcement routes ...
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store.admin');
        Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show.admin');
        Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update.admin');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy.admin');


        // School Configuration Management (already here)
        Route::get('/school-configuration', [SchoolConfigurationController::class, 'show'])->name('school-configuration.show');
        Route::put('/school-configuration', [SchoolConfigurationController::class, 'update'])->name('school-configuration.update');

        // Guardian-Student Link Management (already here)
        Route::get('/guardian-student-links', [GuardianStudentLinkController::class, 'index'])->name('guardian-student-links.index');
        // ... other admin guardian link routes ...
        Route::post('/guardian-student-links', [GuardianStudentLinkController::class, 'store'])->name('guardian-student-links.store');
        Route::delete('/guardian-student-links', [GuardianStudentLinkController::class, 'destroy'])->name('guardian-student-links.destroy');
    });
});