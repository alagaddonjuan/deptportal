<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Timetable') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Day</th>
                                    <th scope="col" class="px-6 py-3">Time</th>
                                    <th scope="col" class="px-6 py-3">Subject</th>
                                    <th scope="col" class="px-6 py-3">Teacher</th>
                                    <th scope="col" class="px-6 py-3">Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($schedules->sortBy(['day_of_week', 'start_time']) as $schedule)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium">{{ ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'][$schedule->day_of_week - 1] }}</td>
                                        <td class="px-6 py-4">{{ date('g:i A', strtotime($schedule->start_time)) }} - {{ date('g:i A', strtotime($schedule->end_time)) }}</td>
                                        <td class="px-6 py-4 font-semibold text-gray-900">{{ $schedule->subject->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">{{ $schedule->teacher->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">{{ $schedule->location }}</td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            Your timetable is not available yet. This is usually because you have not been enrolled in a course.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
