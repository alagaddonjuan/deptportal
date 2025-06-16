<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController; // Make sure this line is at the top
use App\Http\Controllers\Admin\CourseController; // <-- Add this line
use App\Http\Controllers\Admin\SubjectController; // <-- Add this line
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\GuardianStudentLinkController; // <-- Add this line
use App\Http\Controllers\Admin\ClassScheduleController; // <-- Add this line
use App\Http\Controllers\Teacher\TeacherController; // <-- Add this
use App\Http\Controllers\Teacher\AttendanceController; // <-- and this
use App\Http\Controllers\Teacher\TimetableController; // <-- Add this line
use App\Http\Controllers\Teacher\GradeController; // <-- Add this line
use App\Http\Controllers\Teacher\AssignmentController; // <-- Add this line
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- TEACHER ROUTES ---
Route::middleware(['auth', 'verified'])->prefix('teacher')->name('teacher.')->group(function () {

    // ADD THIS NEW ROUTE
    Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable.index');
    Route::get('/', [TeacherController::class, 'index'])->name('index');
    Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::get('/attendance/roster', [AttendanceController::class, 'showRoster'])->name('attendance.roster');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    // ADD THESE NEW ROUTES for Grade Management
    Route::get('/grades/create', [GradeController::class, 'create'])->name('grades.create');
    Route::get('/grades/roster', [GradeController::class, 'showRoster'])->name('grades.roster');
    Route::post('/grades', [GradeController::class, 'store'])->name('grades.store');

     // ADD THESE NEW ROUTES for Assignment Management
    Route::get('/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
});

// --- ADMIN ROUTES ---
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    
    // User Management Resource Route (handles create, store, edit, update, destroy, etc.)
    Route::resource('users', UserController::class);
    
    // Add other admin routes like Course Management here later
    // ADD THIS LINE for Course Management
    Route::resource('courses', CourseController::class);
    Route::resource('subjects', SubjectController::class); // <-- ADD THIS LINE
    Route::resource('enrollments', EnrollmentController::class)->only(['index', 'create', 'store', 'destroy']);
    // ADD THIS LINE for Guardian-Student Links
    Route::resource('guardian-student-links', GuardianStudentLinkController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('timetable', ClassScheduleController::class);

    // School Settings Route
    Route::get('/settings', [SchoolSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SchoolSettingController::class, 'update'])->name('settings.update');

});


require __DIR__.'/auth.php';