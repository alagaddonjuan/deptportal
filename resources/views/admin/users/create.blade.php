<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- Display Validation Errors if any --}}
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Name -->
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700">{{ __('Name') }}</label>
                                <input id="name" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="text" name="name" value="{{ old('name') }}" required autofocus />
                            </div>

                            <!-- Email Address -->
                            <div>
                                <label for="email" class="block font-medium text-sm text-gray-700">{{ __('Email') }}</label>
                                <input id="email" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="email" name="email" value="{{ old('email') }}" required />
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="password" class="block font-medium text-sm text-gray-700">{{ __('Password') }}</label>
                                <input id="password" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="password" name="password" required />
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label for="password_confirmation" class="block font-medium text-sm text-gray-700">{{ __('Confirm Password') }}</label>
                                <input id="password_confirmation" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="password" name="password_confirmation" required />
                            </div>

                            <!-- Role -->
                            <div class="md:col-span-2">
                                <label for="role_id" class="block font-medium text-sm text-gray-700">{{ __('Role') }}</label>
                                <select name="role_id" id="role_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Select a Role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="ms-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                {{ __('Create User') }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>