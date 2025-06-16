<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Timetable Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="flex justify-end mb-4">
                        <a href="{{ route('admin.timetable.create') }}" class="inline-flex ...">
    {{ __('Add New Entry') }}
</a>
                    </div>

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-2 py-3">Time</th>
                                    @foreach ($daysOfWeek as $day)
                                        <th scope="col" class="px-2 py-3 text-center">{{ $day }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($timeSlots as $time)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-2 py-4 font-mono font-semibold text-gray-700">{{ $time }}</td>
                                        @foreach ($daysOfWeek as $dayId => $dayName)
                                            <td class="px-2 py-4 border-l align-middle">
    @if (isset($timetable[$time][$dayId]))
        @php $schedule = $timetable[$time][$dayId]; @endphp
        <div class="text-xs">
            <p class="font-bold text-indigo-700">{{ $schedule->subject->name }}</p>
            <p class="text-gray-600">{{ $schedule->teacher->name ?? 'N/A' }}</p>
            <p class="text-gray-500 italic">Room: {{ $schedule->location }}</p>
            <div class="mt-2">
                <a href="{{ route('admin.timetable.edit', $schedule) }}" class="text-blue-500 hover:text-blue-700 text-xs">Edit</a>
                <form method="POST" action="{{ route('admin.timetable.destroy', $schedule) }}" onsubmit="return confirm('Are you sure?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs ml-2">Delete</button>
                </form>
            </div>
        </div>
    @endif
</td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($daysOfWeek) + 1 }}" class="px-6 py-4 text-center text-gray-500">
                                            No schedule entries found.
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