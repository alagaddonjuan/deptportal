<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Marking Attendance for: {{ $subject->name }} on {{ \Carbon\Carbon::parse($session_date)->format('F j, Y') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('teacher.attendance.store') }}">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                    <input type="hidden" name="session_date" value="{{ $session_date }}">

                    <div class="p-6 text-gray-900">
                        <div class="relative overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Student Name</th>
                                        <th scope="col" class="px-6 py-3 text-center">Status</th>
                                        <th scope="col" class="px-6 py-3">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($students as $student)
                                    <tr class="bg-white border-b">
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ $student->name }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-center space-x-4">
                                                <label class="flex items-center"><input type="radio" name="attendance[{{ $student->id }}]" value="present" class="form-radio" checked> <span class="ml-2">Present</span></label>
                                                <label class="flex items-center"><input type="radio" name="attendance[{{ $student->id }}]" value="absent" class="form-radio"> <span class="ml-2">Absent</span></label>
                                                <label class="flex items-center"><input type="radio" name="attendance[{{ $student->id }}]" value="late" class="form-radio"> <span class="ml-2">Late</span></label>
                                                <label class="flex items-center"><input type="radio" name="attendance[{{ $student->id }}]" value="excused" class="form-radio"> <span class="ml-2">Excused</span></label>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <x-text-input type="text" name="remarks[{{ $student->id }}]" class="w-full text-sm" />
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4">No students are enrolled in this subject's course.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('teacher.attendance.create') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md">Back</a>
                            <x-primary-button class="ms-4">
                                {{ __('Submit Attendance') }}
                            </x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>