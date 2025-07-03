<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CourseController as PublicCourseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\SubjectController as AdminSubjectController;
use App\Http\Controllers\Admin\EnrollmentController as AdminEnrollmentController;
use App\Http\Controllers\Admin\GuardianStudentLinkController as AdminGuardianStudentLinkController;
use App\Http\Controllers\Admin\ClassScheduleController as AdminClassScheduleController;
use App\Http\Controllers\Admin\SchoolSettingController as AdminSchoolSettingController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\TimetableController as TeacherTimetableController;
use App\Http\Controllers\Teacher\GradeController as TeacherGradeController;
use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Teacher\AssignmentController as TeacherAssignmentController;
use App\Http\Controllers\Student\GradeController as StudentGradeController;
use App\Http\Controllers\Student\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\Student\TimetableController as StudentTimetableController;
use App\Http\Controllers\Parent\DashboardController as ParentDashboardController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\NotificationController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/courses', [PublicCourseController::class, 'index'])->name('courses.index');
    // ADD THIS ENTIRE ROUTE GROUP FOR NOTIFICATIONS
    Route::prefix('notifications')->name('notifications.')->group(function() {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('markAsRead');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
    });
});

// --- ADMIN-ONLY ROUTES ---
Route::middleware(['auth', 'verified', 'can:is-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('index');
    Route::resource('users', AdminUserController::class);
    Route::resource('courses', AdminCourseController::class);
    Route::resource('subjects', AdminSubjectController::class);
    Route::resource('enrollments', AdminEnrollmentController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('guardian-student-links', AdminGuardianStudentLinkController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('timetable', AdminClassScheduleController::class);
    Route::get('/settings', [AdminSchoolSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [AdminSchoolSettingController::class, 'update'])->name('settings.update');
});

// --- TEACHER-ONLY ROUTES ---
Route::middleware(['auth', 'verified'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/', [TeacherDashboardController::class, 'index'])->name('index');
    Route::get('/tools', [\App\Http\Controllers\Teacher\ToolController::class, 'index'])->name('tools');
    Route::get('/timetable', [TeacherTimetableController::class, 'index'])->name('timetable.index');
    Route::get('/grades/create', [TeacherGradeController::class, 'create'])->name('grades.create');
    Route::get('/grades/roster', [TeacherGradeController::class, 'showRoster'])->name('grades.roster');
    Route::post('/grades', [TeacherGradeController::class, 'store'])->name('grades.store');
    Route::get('/assignments/create', [TeacherAssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [TeacherAssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/attendance', [TeacherAttendanceController::class, 'create'])->name('attendance.create');
    Route::get('/attendance/roster', [TeacherAttendanceController::class, 'showRoster'])->name('attendance.roster');
    Route::post('/attendance', [TeacherAttendanceController::class, 'store'])->name('attendance.store');
});

// --- STUDENT-ONLY ROUTES ---
Route::middleware(['auth', 'verified'])->prefix('student')->name('student.')->group(function () {
    Route::get('/', [StudentDashboardController::class, 'index'])->name('index');
    Route::get('/my-grades', [StudentGradeController::class, 'index'])->name('grades.index');
    Route::get('/my-attendance', [StudentAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/my-timetable', [StudentTimetableController::class, 'index'])->name('timetable.index');
});

// --- PARENT-ONLY ROUTES ---
Route::middleware(['auth', 'verified'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('/my-children', [ParentDashboardController::class, 'myChildren'])->name('my-children');
    Route::get('/my-children/{student}/grades', [ParentDashboardController::class, 'showChildGrades'])->name('my-children.grades');
    Route::get('/my-children/{student}/attendance', [ParentDashboardController::class, 'showChildAttendance'])->name('my-children.attendance');
     // ADD THIS NEW ROUTE
    Route::get('/my-children/{student}/timetable', [ParentDashboardController::class, 'showChildTimetable'])->name('my-children.timetable');
});


require __DIR__.'/auth.php';