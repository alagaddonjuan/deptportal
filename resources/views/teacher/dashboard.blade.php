<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Teacher Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium">Today's Schedule ({{ now()->format('l, F jS') }})</h3>
                    
                    @if($todaysSchedule->isNotEmpty())
                        <div class="mt-4 space-y-4">
                            @foreach($todaysSchedule as $schedule)
                                <div class="p-4 border dark:border-gray-700 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $schedule->subject->name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $schedule->subject->course->name }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-mono text-sm">{{ date('g:i A', strtotime($schedule->start_time)) }} - {{ date('g:i A', strtotime($schedule->end_time)) }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Room: {{ $schedule->location }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-4 text-gray-500 dark:text-gray-400">You have no classes scheduled for today.</p>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>