<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('All Notifications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    @if($notifications->isEmpty())
                        <p>You have no notifications.</p>
                    @else
                        <ul class="space-y-4">
                            @foreach($notifications as $notification)
                                <li class="p-4 rounded-md {{ $notification->read_at ? 'bg-gray-50 dark:bg-gray-700/50' : 'bg-blue-50 dark:bg-blue-900/20 border border-blue-300 dark:border-blue-700' }}">
                                    <p class="font-medium text-gray-800 dark:text-gray-200">
                                        {{ $notification->data['message'] }}
                                    </p>
                                    <small class="text-gray-500 dark:text-gray-400">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="mt-6">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>