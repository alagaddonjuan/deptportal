<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Grades for: <span class="text-indigo-600">{{ $student->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('parent.my-children') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    &larr; Back to My Children
                </a>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="border-b border-gray-200 mb-4">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="{{ route('parent.my-children.grades', $student) }}" class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" aria-current="page">
                    Grades
                </a>
                <a href="{{ route('parent.my-children.attendance', $student) }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Attendance
                </a>
                <a href="{{ route('parent.my-children.timetable', $student) }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
            Timetable
        </a>
            </nav>
        </div>
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Subject</th>
                                    <th scope="col" class="px-6 py-3">Assessment</th>
                                    <th scope="col" class="px-6 py-3 text-center">Marks Obtained</th>
                                    <th scope="col" class="px-6 py-3 text-center">Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($grades as $grade)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium text-gray-900">
                                            {{ $grade->assessment->subject->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $grade->assessment->title ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            {{ $grade->marks_obtained }} / {{ $grade->assessment->max_marks }}
                                        </td>
                                        <td class="px-6 py-4 text-center font-semibold">
                                            @if($grade->assessment->max_marks > 0)
                                                @php
                                                    $percentage = ($grade->marks_obtained / $grade->assessment->max_marks) * 100;
                                                    $letter = 'F';
                                                    if ($percentage >= 90) $letter = 'A';
                                                    elseif ($percentage >= 80) $letter = 'B';
                                                    elseif ($percentage >= 70) $letter = 'C';
                                                    elseif ($percentage >= 60) $letter = 'D';
                                                @endphp
                                                {{ $letter }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b">
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                            No grades have been recorded for this student yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <div class="mt-4">
                        {{ $grades->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>