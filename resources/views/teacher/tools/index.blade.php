<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Teacher Tools') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
             @if (session('success'))
                <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Mark Attendance Card -->
                <a href="{{ route('teacher.attendance.create') }}" class="block p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow hover:bg-gray-100 dark:hover:bg-gray-700">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Mark Attendance</h5>
                    <p class="font-normal text-gray-700 dark:text-gray-400">Record daily attendance for your classes.</p>
                </a>
                
                <!-- Manage Grades Card -->
                <a href="{{ route('teacher.grades.create') }}" class="block p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow hover:bg-gray-100 dark:hover:bg-gray-700">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Manage Grades</h5>
                    <p class="font-normal text-gray-700 dark:text-gray-400">Enter and update student marks for assessments.</p>
                </a>

                <!-- Create Assignment Card -->
                <a href="{{ route('teacher.assignments.create') }}" class="block p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow hover:bg-gray-100 dark:hover:bg-gray-700">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Create Assignment</h5>
                    <p class="font-normal text-gray-700 dark:text-gray-400">Add new assignments and assessments for your subjects.</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>