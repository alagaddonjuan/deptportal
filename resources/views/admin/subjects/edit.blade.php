<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Subject: {{ $subject->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

                    <form method="POST" action="{{ route('admin.subjects.update', $subject) }}">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                             <div>
                                <x-input-label for="name" :value="__('Subject Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $subject->name)" required autofocus />
                            </div>
                            <div>
                                <x-input-label for="code" :value="__('Subject Code')" />
                                <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code', $subject->code)" required />
                            </div>
                             <div>
                                <x-input-label for="course_id" :value="__('Course')" />
                                <select name="course_id" id="course_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}" @selected(old('course_id', $subject->course_id) == $course->id)>{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                             <div>
                                <x-input-label for="teacher_id" :value="__('Teacher')" />
                                <select name="teacher_id" id="teacher_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Not Assigned</option>
                                     @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" @selected(old('teacher_id', $subject->teacher_id) == $teacher->id)>{{ $teacher->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                             <div>
                                <x-input-label for="credits" :value="__('Credits')" />
                                <x-text-input id="credits" class="block mt-1 w-full" type="number" name="credits" :value="old('credits', $subject->credits)" required />
                            </div>
                            <div>
                                <x-input-label for="type" :value="__('Type')" />
                                <select name="type" id="type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="core" @selected(old('type', $subject->type) == 'core')>Core</option>
                                    <option value="elective" @selected(old('type', $subject->type) == 'elective')>Elective</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="description" :value="__('Description')" />
                                <textarea name="description" id="description" rows="4" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $subject->description) }}</textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                             <a href="{{ route('admin.subjects.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md">Cancel</a>
                            <x-primary-button class="ms-4">Update Subject</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>