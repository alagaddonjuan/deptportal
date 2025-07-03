<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('School Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session('success'))
                        <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- School Name -->
                            <div>
                                <x-input-label for="school_name" :value="__('School Name')" />
                                <x-text-input id="school_name" class="block mt-1 w-full" type="text" name="school_name" :value="old('school_name', $settings->school_name)" />
                            </div>

                            <!-- School Email -->
                            <div>
                                <x-input-label for="school_email" :value="__('School Email')" />
                                <x-text-input id="school_email" class="block mt-1 w-full" type="email" name="school_email" :value="old('school_email', $settings->school_email)" />
                            </div>

                            <!-- School Phone -->
                            <div>
                                <x-input-label for="school_phone" :value="__('School Phone')" />
                                <x-text-input id="school_phone" class="block mt-1 w-full" type="text" name="school_phone" :value="old('school_phone', $settings->school_phone)" />
                            </div>

                             <!-- School Address -->
                            <div>
                                <x-input-label for="school_address" :value="__('School Address')" />
                                <x-text-input id="school_address" class="block mt-1 w-full" type="text" name="school_address" :value="old('school_address', $settings->school_address)" />
                            </div>

                             <!-- Academic Year -->
                            <div>
                                <x-input-label for="current_academic_year" :value="__('Current Academic Year')" />
                                <x-text-input id="current_academic_year" class="block mt-1 w-full" type="text" name="current_academic_year" :value="old('current_academic_year', $settings->current_academic_year)" placeholder="e.g., 2024-2025"/>
                            </div>

                             <!-- Term / Semester -->
                             <div>
                                <x-input-label for="current_term_semester" :value="__('Current Term/Semester')" />
                                <x-text-input id="current_term_semester" class="block mt-1 w-full" type="text" name="current_term_semester" :value="old('current_term_semester', $settings->current_term_semester)" placeholder="e.g., First Term"/>
                            </div>

                            <!-- School Logo -->
                            <div class="md:col-span-2">
                                <x-input-label for="school_logo_file" :value="__('School Logo')" />
                                @if ($settings->school_logo_path)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $settings->school_logo_path) }}" alt="Current Logo" class="h-16 rounded">
                                        <div class="mt-2 text-xs">
                                            <input type="checkbox" name="remove_school_logo" id="remove_school_logo">
                                            <label for="remove_school_logo">Remove current logo</label>
                                        </div>
                                    </div>
                                @endif
                                <input id="school_logo_file" name="school_logo_file" type="file" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none mt-2">
                                <p class="mt-1 text-sm text-gray-500">PNG, JPG, GIF up to 2MB.</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Save Settings') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>