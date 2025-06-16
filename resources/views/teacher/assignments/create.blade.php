<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Assignment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('teacher.assignments.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Subject -->
                            <div class="md:col-span-2">
                                <x-input-label for="subject_id" :value="__('Subject')" />
                                <select name="subject_id" id="subject_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Select a Subject</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Title -->
                            <div class="md:col-span-2">
                                <x-input-label for="title" :value="__('Assignment Title')" />
                                <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required />
                            </div>

                            <!-- Due Date & Max Marks -->
                            <div>
                                <x-input-label for="due_date" :value="__('Due Date')" />
                                <x-text-input id="due_date" class="block mt-1 w-full" type="datetime-local" name="due_date" :value="old('due_date')" />
                            </div>
                            <div>
                                <x-input-label for="max_marks" :value="__('Max Marks')" />
                                <x-text-input id="max_marks" class="block mt-1 w-full" type="number" name="max_marks" :value="old('max_marks')" />
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <x-input-label for="description" :value="__('Description')" />
                                <textarea name="description" id="description" rows="5" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('description') }}</textarea>
                            </div>
                            
                            <!-- File Upload -->
                            <div class="md:col-span-2">
                                <x-input-label for="assignment_file" :value="__('Assignment File (Optional)')" />
                                <input id="assignment_file" name="assignment_file" type="file" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none mt-1">
                                <p class="mt-1 text-sm text-gray-500">PDF, DOC, DOCX, JPG, PNG (Max: 10MB)</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                             <a href="{{ route('teacher.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md">Cancel</a>
                            <x-primary-button class="ms-4">Create Assignment</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>