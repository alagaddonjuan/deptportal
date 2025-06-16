<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;

class CourseManagementTest extends TestCase
{
    use RefreshDatabase; // Use this trait to reset the DB for each test

    /**
     * Test that an admin can successfully create a new course.
     *
     * @return void
     */
    public function test_admin_can_create_a_course(): void
    {
        // 1. Arrange: Set up the necessary state
        $adminRole = Role::factory()->create(['slug' => 'admin']);
        $adminUser = User::factory()->create(['role_id' => $adminRole->id]);

        $courseData = [
            'name' => 'Test Course in Computer Science',
            'code' => 'TCCS101',
            'level' => 'Undergraduate',
            'status' => 'active',
        ];

        // 2. Act: Perform the action we want to test
        $response = $this->actingAs($adminUser, 'sanctum') // Authenticate as the admin user
                         ->postJson('/api/courses', $courseData); // Make the API call

        // 3. Assert: Check if the outcome is what we expected
        $response->assertStatus(201) // Check for HTTP 201 Created status
                 ->assertJson([ // Check if the response JSON contains this structure/data
                     'message' => 'Course created successfully.',
                     'course' => [
                         'name' => 'Test Course in Computer Science',
                         'code' => 'TCCS101',
                         'status' => 'active',
                     ]
                 ]);

        // Assert that the course was actually created in the database
        $this->assertDatabaseHas('courses', [
            'code' => 'TCCS101',
            'name' => 'Test Course in Computer Science',
        ]);
    }

    /**
     * Test that a non-admin user cannot create a course.
     *
     * @return void
     */
    public function test_non_admin_cannot_create_a_course(): void
    {
        // 1. Arrange
        $studentRole = Role::factory()->create(['slug' => 'student']);
        $studentUser = User::factory()->create(['role_id' => $studentRole->id]);

        $courseData = [
            'name' => 'Unauthorized Test Course',
            'code' => 'UTC101',
            'status' => 'active',
        ];

        // 2. Act
        $response = $this->actingAs($studentUser, 'sanctum') // Authenticate as a student
                         ->postJson('/api/courses', $courseData);

        // 3. Assert
        $response->assertStatus(403); // Check for HTTP 403 Forbidden status

        // Assert that the course was NOT created in the database
        $this->assertDatabaseMissing('courses', [
            'code' => 'UTC101',
        ]);
    }
}
