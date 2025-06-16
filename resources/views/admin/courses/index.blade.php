<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Course Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    {{-- Success Message --}}
                    @if (session('success'))
                        <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="flex justify-end mb-4">
                        <a href="{{ route('admin.courses.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Add New Course') }}
                        </a>
                    </div>

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Name</th>
                                    <th scope="col" class="px-6 py-3">Code</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                               @forelse ($courses as $course)
        <tr class="bg-white border-b hover:bg-gray-50">
            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                {{ $course->name }}
            </th>
            <td class="px-6 py-4">
                {{ $course->code }}
            </td>
            <td class="px-6 py-4">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $course->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ ucfirst($course->status) }}
                </span>
            </td>
            <td class="px-6 py-4 text-right">
    <div class="flex items-center justify-end">
        <a href="{{ route('admin.courses.edit', $course) }}" class="font-medium text-blue-600 hover:underline">Edit</a>
        <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" onsubmit="return confirm('Are you sure you want to delete this course? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="font-medium text-red-600 hover:underline ml-4">
                Delete
            </button>
        </form>
    </div>
</td>
        </tr>
    @empty
        <tr class="bg-white border-b">
            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                No courses found.
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