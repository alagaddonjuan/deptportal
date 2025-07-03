<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    {{-- === ADMIN LINKS === --}}
                    @if(auth()->user()->isAdmin())
    <x-nav-link :href="route('admin.index')" :active="request()->routeIs('admin.index')">
        {{ __('Admin Dashboard') }}
    </x-nav-link>
    <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
        {{ __('User Mgt') }}
    </x-nav-link>
    <x-nav-link :href="route('admin.courses.index')" :active="request()->routeIs('admin.courses.*')">
        {{ __('Course Mgt') }}
    </x-nav-link>
    <x-nav-link :href="route('admin.subjects.index')" :active="request()->routeIs('admin.subjects.*')">
        {{ __('Subject Mgt') }}
    </x-nav-link>
    <x-nav-link :href="route('admin.enrollments.index')" :active="request()->routeIs('admin.enrollments.*')">
        {{ __('Enrollments') }}
    </x-nav-link>
    <x-nav-link :href="route('admin.guardian-student-links.index')" :active="request()->routeIs('admin.guardian-student-links.*')">
        {{ __('Guardian Links') }}
    </x-nav-link>
    <x-nav-link :href="route('admin.timetable.index')" :active="request()->routeIs('admin.timetable.*')">
        {{ __('Timetable') }}
    </x-nav-link>
@endif

                    {{-- === TEACHER LINKS === --}}
                    @if(auth()->user()->isTeacher())
                        <x-nav-link :href="route('teacher.timetable.index')" :active="request()->routeIs('teacher.timetable.*')">
                            {{ __('My Timetable') }}
                        </x-nav-link>
                        <x-nav-link :href="route('teacher.tools')" :active="request()->routeIs('teacher.tools') || request()->routeIs('teacher.attendance.*') || request()->routeIs('teacher.grades.*') || request()->routeIs('teacher.assignments.*')">
        {{ __('Teacher Tools') }}
    </x-nav-link>
@endif

                    
                    {{-- === STUDENT LINKS === --}}
                    @if(auth()->user()->isStudent())
                        <x-nav-link :href="route('student.timetable.index')" :active="request()->routeIs('student.timetable.*')">
                            {{ __('My Timetable') }}
                        </x-nav-link>
                        <x-nav-link :href="route('student.grades.index')" :active="request()->routeIs('student.grades.*')">
                            {{ __('My Grades') }}
                        </x-nav-link>
                        <x-nav-link :href="route('student.attendance.index')" :active="request()->routeIs('student.attendance.*')">
                            {{ __('My Attendance') }}
                        </x-nav-link>
                    @endif

                    {{-- === PARENT LINKS === --}}
                    @if(auth()->user()->isParent())
                        <x-nav-link :href="route('parent.my-children')" :active="request()->routeIs('parent.my-children')">
                            {{ __('My Children') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown and Theme Toggle -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                 <!-- Theme Toggle Button -->
                <button @click="darkMode = !darkMode" class="p-2 rounded-full text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                    <svg x-show="!darkMode" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m8.66-15.66l-.707.707M4.34 19.66l-.707.707M21 12h-1M4 12H3m15.66 8.66l-.707-.707M4.34 4.34l-.707-.707" /></svg>
                    <svg x-show="darkMode" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                </button>
                <!-- Notifications Dropdown -->
                <div x-data="{
                        open: false,
                        notifications: [],
                        unreadCount: 0,
                        fetchNotifications() {
                            fetch('{{ route('notifications.index') }}?json=true', {
                                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${localStorage.getItem('authToken')}` }
                            })
                                .then(response => response.json())
                                .then(data => {
                                    this.notifications = data.data;
                                    this.unreadCount = data.unread_count;
                                })
                                .catch(error => console.error('Error fetching notifications:', error));
                        }
                    }" 
                    x-init="fetchNotifications(); setInterval(() => fetchNotifications(), 60000)"
                    class="relative ms-3"
                >
                    <button @click="open = !open" class="relative p-2 rounded-full text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span x-show="unreadCount > 0" class="absolute top-0 right-0 -mt-1 -mr-1 px-1.5 py-0.5 bg-red-600 text-white text-xs rounded-full" x-text="unreadCount" style="display: none;"></span>
                    </button>

                    <div x-show="open" @click.away="open = false"
                         x-transition
                         class="absolute right-0 z-50 mt-2 w-80 origin-top-right rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                         style="display: none;">
                        <div class="p-2">
                            <div class="flex justify-between items-center px-2 py-1">
                                <span class="font-semibold text-sm text-gray-700 dark:text-gray-200">Notifications</span>
                                <form method="POST" action="{{ route('notifications.markAllAsRead') }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-blue-500 hover:underline">Mark all as read</button>
                                </form>
                            </div>
                            <div class="mt-2 max-h-80 overflow-y-auto">
                                <template x-for="notification in notifications" :key="notification.id">
                                    <form method="POST" :action="`/notifications/${notification.id}/read`">
                                        @csrf
                                        <button type="submit" class="w-full text-left p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <p class="text-sm text-gray-800 dark:text-gray-200" x-text="notification.data.message"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="new Date(notification.created_at).toLocaleString()"></p>
                                        </button>
                                    </form>
                                </template>
                                <template x-if="notifications.length === 0">
                                    <p class="text-center text-sm text-gray-500 dark:text-gray-400 py-4">No new notifications</p>
                                </template>
                            </div>
                            <div class="border-t border-gray-200 dark:border-gray-700 mt-2 pt-2 text-center">
                                <a href="{{ route('notifications.index') }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                    View all notifications
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1"><svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        @if(auth()->user()->isAdmin())
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4"><div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ __('Admin Panel') }}</div></div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('admin.index')">{{ __('Admin Dashboard') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.users.index')">{{ __('User Management') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.courses.index')">{{ __('Course Management') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.subjects.index')">{{ __('Subject Management') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.enrollments.index')">{{ __('Enrollments') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.guardian-student-links.index')">{{ __('Guardian Links') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.timetable.index')">{{ __('Timetable') }}</x-responsive-nav-link>
                </div>
            </div>
        @endif
        @if(auth()->user()->isTeacher())
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4"><div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ __('Teacher Menu') }}</div></div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('teacher.timetable.index')">{{ __('My Timetable') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('teacher.index')">{{ __('Teacher Tools') }}</x-responsive-nav-link>
                </div>
            </div>
        @endif
        @if(auth()->user()->isStudent())
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4"><div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ __('Student Menu') }}</div></div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('student.timetable.index')">{{ __('My Timetable') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('student.grades.index')">{{ __('My Grades') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('student.attendance.index')">{{ __('My Attendance') }}</x-responsive-nav-link>
                </div>
            </div>
        @endif
        @if(auth()->user()->isParent())
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4"><div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ __('Parent Menu') }}</div></div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('parent.my-children')">{{ __('My Children') }}</x-responsive-nav-link>
                </div>
            </div>
        @endif

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>