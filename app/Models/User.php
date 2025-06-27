<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdmin(): bool { return $this->role?->slug === 'admin'; }
    public function isTeacher(): bool { return $this->role?->slug === 'teacher'; }
    public function isStudent(): bool { return $this->role?->slug === 'student'; }
    public function isParent(): bool { return $this->role?->slug === 'parent'; }

    /**
     * Get the grades for the user (if they are a student).
     */
    public function grades()
    {
        // CORRECTED: This now uses the correct column name from the grades migration
        return $this->hasMany(Grade::class, 'student_user_id');
    }

    /**
     * Get the attendance records for the user (if they are a student).
     */
    public function attendances()
    {
        // CORRECTED: This now uses the correct column name from the attendances migration
        return $this->hasMany(Attendance::class, 'student_user_id');
    }

    /**
     * Get the course enrollments for the user (if they are a student).
     */
    public function enrollments()
    {
        // CORRECTED: This uses the correct column name from the enrollments migration
        return $this->hasMany(Enrollment::class, 'user_id');
    }

    /**
     * Get the student links for the user (if they are a guardian).
     */
    public function linkedStudents()
    {
        return $this->hasMany(GuardianStudentLink::class, 'guardian_user_id');
    }

}
