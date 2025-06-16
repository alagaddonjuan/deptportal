<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Link Guardian to Student') }}
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

                    <form method="POST" action="{{ route('admin.guardian-student-links.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Guardian -->
                            <div>
                                <x-input-label for="guardian_user_id" :value="__('Guardian/Parent')" />
                                <select name="guardian_user_id" id="guardian_user_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Select a Guardian</option>
                                    @foreach ($guardians as $guardian)
                                        <option value="{{ $guardian->id }}" @selected(old('guardian_user_id') == $guardian->id)>
                                            {{ $guardian->name }} ({{ $guardian->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Student -->
                            <div>
                                <x-input-label for="student_user_id" :value="__('Student')" />
                                <select name="student_user_id" id="student_user_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Select a Student</option>
                                    @foreach ($students as $student)
                                        <option value="{{ $student->id }}" @selected(old('student_user_id') == $student->id)>
                                            {{ $student->name }} ({{ $student->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Relationship Type -->
                            <div class="md:col-span-2">
                                <x-input-label for="relationship_type" :value="__('Relationship (e.g., Father, Mother, Guardian)')" />
                                <x-text-input id="relationship_type" class="block mt-1 w-full" type="text" name="relationship_type" :value="old('relationship_type')" required />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                             <a href="{{ route('admin.guardian-student-links.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md">Cancel</a>
                            <x-primary-button class="ms-4">Create Link</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>