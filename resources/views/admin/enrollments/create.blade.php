<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Enroll Student in a Course') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if ($errors->any() || session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                            <ul class="list-disc list-inside">
                                @if(session('error'))
                                    <li>{{ session('error') }}</li>
                                @endif
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.enrollments.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Student -->
                            <div>
                                <x-input-label for="student_user_id" :value="__('Student')" />
                                <select name="student_user_id" id="student_user_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Select a Student</option>
                                    @foreach ($students as $student)
                                        <option value="{{ $student->id }}" @selected(old('student_user_id') == $student->id)>
                                            {{ $student->name }} ({{ $student->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Course -->
                            <div>
                                <x-input-label for="course_id" :value="__('Course')" />
                                <select name="course_id" id="course_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Select a Course</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Status -->
                             <div>
                                <x-input-label for="status" :value="__('Enrollment Status')" />
                                <select name="status" id="status" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="enrolled" selected>Enrolled</option>
                                    <option value="withdrawn">Withdrawn</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                             <a href="{{ route('admin.enrollments.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md">Cancel</a>
                            <x-primary-button class="ms-4">Enroll Student</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>