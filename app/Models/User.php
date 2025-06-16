<?php

namespace App\Models;


use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification; // Import at the top
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Grade; // Add this
use App\Models\Attendance; // Add this
use App\Models\ClassSchedule; // Add this
use App\Models\Assignment; // Add this import
use App\Models\Submission; // Add this import
use App\Models\Announcement; // Add this import



class User extends Authenticatable 
implements MustVerifyEmail

{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id', // Ensure this is fillable if you assign it directly during user creation
        'status',  // Ensure this is fillable
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the role associated with the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the profile associated with the user.
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }


    /**
     * Helper method to check if the user has a specific role.
     *
     * @param string $roleSlug
     * @return bool
     */

     public function hasRole(string $roleSlug): bool
    {
        return $this->role && $this->role->slug === $roleSlug;
    }

    public function sendPasswordResetNotification($token)
{
    // Construct the URL for your frontend password reset page
    // Example: https://your-frontend-app.com/reset-password?token=THE_TOKEN&email=USER_EMAIL
    // The 'email' parameter is often useful for the frontend to pre-fill the email field.
    $frontendUrl = config('app.frontend_url', 'http://127.0.0.1:8000'); // Get from config or .env
    $url = $frontendUrl . '/reset-password?token=' . $token . '&email=' . urlencode($this->getEmailForPasswordReset());

    // $this->notify(new ResetPasswordNotification($url)); // Pass the custom URL
    $this->notify(new ResetPasswordNotification($token));

}

    // Convenience methods for role checks (optional but useful)
    public function isAdmin(): bool { return $this->hasRole('admin'); }
    public function isTeacher(): bool {  return $this->role->slug === 'teacher'; }
    public function isStudent(): bool { return $this->hasRole('student'); }
    public function isParent(): bool { return $this->hasRole('parent'); }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the courses the user is enrolled in.
     * This uses the enrollments table as the pivot table.
     */
    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments', 'user_id', 'course_id')
                    ->withPivot('enrollment_date', 'status', 'academic_year', 'semester_term', 'notes')
                    ->withTimestamps(); // To manage created_at/updated_at on the pivot table
    }

    /**
     * Get the grades entered by this user (teacher).
     */
    public function gradesMarked(): HasMany
    {
        return $this->hasMany(Grade::class, 'graded_by_user_id');
    }

    /**
     * Get the attendance records marked by this user (teacher).
     */
    public function attendancesMarked(): HasMany
    {
        return $this->hasMany(Attendance::class, 'marked_by_user_id');
    }

    /**
 * Get all class schedule entries assigned to this user (teacher).
 */
public function classSchedulesAsTeacher(): HasMany
{
    return $this->hasMany(ClassSchedule::class, 'teacher_id');
}

 /**
     * Get the assignments created by this user (if they are a teacher).
     */
    public function assignmentsCreated(): HasMany
    {
        return $this->hasMany(Assignment::class, 'teacher_id');
    }

    /**
     * Get the submissions made by this user (if they are a student).
     */
    public function submissionsMade(): HasMany
    {
        return $this->hasMany(Submission::class, 'student_id');
    }

    /**
     * Get the submissions graded by this user (if they are a teacher).
     */
    public function submissionsGraded(): HasMany
    {
        return $this->hasMany(Submission::class, 'graded_by_user_id');
    }

    /**
     * Get the announcements created by this user (if they are a teacher).
     *//**
 * Get the announcements created by this user.
 */
public function announcements(): HasMany
{
    return $this->hasMany(Announcement::class);
}
/**
     * The student users that belong to this guardian user.
     * This method should be called on a User instance that is a guardian.
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class, // The related model is User (for students)
            'guardian_student_links', // The name of the pivot table
            'guardian_user_id',      // Foreign key on the pivot table related to the current model (Guardian)
            'student_user_id'        // Foreign key on the pivot table related to the User model (Student)
        )->withPivot('relationship_type', 'is_primary_contact', 'can_access_records')
         ->withTimestamps(); // To manage created_at/updated_at on the pivot table
    }

    /**
     * The guardian users that are linked to this student user.
     * This method should be called on a User instance that is a student.
     */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class, // The related model is User (for guardians)
            'guardian_student_links', // The name of the pivot table
            'student_user_id',       // Foreign key on the pivot table related to the current model (Student)
            'guardian_user_id'       // Foreign key on the pivot table related to the User model (Guardian)
        )->withPivot('relationship_type', 'is_primary_contact', 'can_access_records')
         ->withTimestamps(); // To manage created_at/updated_at on the pivot table
    }
    
}
