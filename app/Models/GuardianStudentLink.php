<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuardianStudentLink extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'guardian_user_id',
        'student_user_id',
        'relationship_type',
    ];

    /**
     * Get the guardian (parent) associated with the link.
     */
    public function guardian()
    {
        return $this->belongsTo(User::class, 'guardian_user_id');
    }

    /**
     * Get the student associated with the link.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }
}