<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Timetable Entry') }}
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

                    <form method="POST" action="{{ route('admin.timetable.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Subject -->
                            <div>
                                <x-input-label for="subject_id" :value="__('Subject')" />
                                <select name="subject_id" id="subject_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Select a Subject</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>
                                            {{ $subject->name }} ({{ $subject->course->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Teacher -->
                            <div>
                                <x-input-label for="teacher_id" :value="__('Teacher')" />
                                <select name="teacher_id" id="teacher_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Select a Teacher</option>
                                     @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>
                                            {{ $teacher->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Day of Week -->
                            <div>
                                <x-input-label for="day_of_week" :value="__('Day of the Week')" />
                                <select name="day_of_week" id="day_of_week" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Select a Day</option>
                                    @foreach ($daysOfWeek as $id => $day)
                                        <option value="{{ $id }}" @selected(old('day_of_week') == $id)>{{ $day }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Location -->
                             <div>
                                <x-input-label for="location" :value="__('Location / Room')" />
                                <x-text-input id="location" class="block mt-1 w-full" type="text" name="location" :value="old('location')" required />
                            </div>

                            <!-- Start Time -->
                            <div>
                                <x-input-label for="start_time" :value="__('Start Time')" />
                                <x-text-input id="start_time" class="block mt-1 w-full" type="time" name="start_time" :value="old('start_time')" required />
                            </div>

                            <!-- End Time -->
                            <div>
                                <x-input-label for="end_time" :value="__('End Time')" />
                                <x-text-input id="end_time" class="block mt-1 w-full" type="time" name="end_time" :value="old('end_time')" required />
                            </div>

                        </div>

                        <div class="flex items-center justify-end mt-6">
                             <a href="{{ route('admin.timetable.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md">Cancel</a>
                            <x-primary-button class="ms-4">Add Entry</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>