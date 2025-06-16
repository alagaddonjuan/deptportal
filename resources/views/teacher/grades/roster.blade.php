<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Enter Grades for: <span class="text-indigo-600">{{ $assessment->title }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('teacher.grades.store') }}">
                    @csrf
                    <input type="hidden" name="assessment_id" value="{{ $assessment->id }}">

                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-4">
                           <p class="text-sm text-gray-600">Subject: <strong>{{ $assessment->subject->name }}</strong></p>
                           <p class="text-sm text-gray-600">Max Marks: <strong>{{ $assessment->max_marks }}</strong></p>
                        </div>

                        <div class="relative overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Student Name</th>
                                        <th scope="col" class="px-6 py-3" style="width: 200px;">Marks Obtained</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($students as $student)
                                    <tr class="bg-white border-b">
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ $student->name }}</td>
                                        <td class="px-6 py-4">
                                            <x-text-input type="number" step="0.5" min="0" max="{{ $assessment->max_marks }}"
                                                name="grades[{{ $student->id }}]" class="w-full text-sm" 
                                                value="{{ $existingGrades[$student->id] ?? '' }}" />
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-4">No students are enrolled in this subject's course.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('teacher.grades.create') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md">Back</a>
                            <x-primary-button class="ms-4">
                                {{ __('Save Grades') }}
                            </x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>