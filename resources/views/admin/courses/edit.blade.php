<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Course: {{ $course->name }}
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

                    <form method="POST" action="{{ route('admin.courses.update', $course) }}">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Course Name -->
                            <div>
                                <x-input-label for="name" :value="__('Course Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $course->name)" required autofocus />
                            </div>

                            <!-- Course Code -->
                            <div>
                                <x-input-label for="code" :value="__('Course Code')" />
                                <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code', $course->code)" required />
                            </div>

                            <!-- Level -->
                            <div>
                                <x-input-label for="level" :value="__('Level')" />
                                <x-text-input id="level" class="block mt-1 w-full" type="text" name="level" :value="old('level', $course->level)" />
                            </div>

                            <!-- Duration -->
                            <div>
                                <x-input-label for="duration_years" :value="__('Duration (Years)')" />
                                <x-text-input id="duration_years" class="block mt-1 w-full" type="number" name="duration_years" :value="old('duration_years', $course->duration_years)" />
                            </div>

                             <!-- Status -->
                            <div class="md:col-span-2">
                                <x-input-label for="status" :value="__('Status')" />
                                <select name="status" id="status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="active" @selected(old('status', $course->status) == 'active')>Active</option>
                                    <option value="inactive" @selected(old('status', $course->status) == 'inactive')>Inactive</option>
                                    <option value="archived" @selected(old('status', $course->status) == 'archived')>Archived</option>
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <x-input-label for="description" :value="__('Description')" />
                                <textarea name="description" id="description" rows="4" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $course->description) }}</textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                             <a href="{{ route('admin.courses.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __('Update Course') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>