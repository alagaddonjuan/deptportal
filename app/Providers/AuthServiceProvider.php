<?php

namespace App\Providers;

// Import your models and policies that you will register
use App\Models\Assessment;
use App\Policies\AssessmentPolicy;
use App\Models\Grade;             // <<< ADD THIS LINE
use App\Policies\GradePolicy;       // <<< ADD THIS LINE
use App\Models\Attendance;        // <<< ADD THIS LINE
use App\Policies\AttendancePolicy;  // <<< ADD THIS LINE
// use Illuminate\Support\Facades\Gate; // Uncomment if you define Gates directly here later
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy', // Default Laravel example
        // Add your policy mappings here. For example:
         Assessment::class => AssessmentPolicy::class,
         Grade::class => GradePolicy::class, // Add this line
         Attendance::class => AttendancePolicy::class, // Add this line
    // ... other policies
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies(); // This line is important for auto-discovery if not using the $policies array directly.
                                // However, explicitly listing in $policies is clearer.

        // Example of defining a Gate directly:
        // Gate::define('edit-settings', function (User $user) {
        //     return $user->isAdmin();
        // });
    }
    
}