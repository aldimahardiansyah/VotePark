<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }} - {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(auth()->user()->isSuperAdmin())
                <!-- Super Admin Dashboard -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-semibold mb-2">Total Sites</h3>
                            <p class="text-3xl font-bold text-blue-600">{{ \App\Models\Site::count() }}</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-semibold mb-2">Total Users</h3>
                            <p class="text-3xl font-bold text-green-600">{{ \App\Models\User::count() }}</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-semibold mb-2">Total Events</h3>
                            <p class="text-3xl font-bold text-purple-600">{{ \App\Models\Event::count() }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <a href="{{ route('event.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                                Manage Events
                            </a>
                            <a href="{{ route('unit.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                                Manage Units
                            </a>
                            <a href="{{ route('question.index') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center">
                                Manage Questions
                            </a>
                            <a href="#" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-center">
                                System Settings
                            </a>
                        </div>
                    </div>
                </div>

            @elseif(auth()->user()->isAdminSite())
                <!-- Site Admin Dashboard -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-2">{{ auth()->user()->site->name }}</h3>
                        <p class="text-gray-600">{{ auth()->user()->site->description }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-semibold mb-2">Site Events</h3>
                            <p class="text-3xl font-bold text-blue-600">{{ auth()->user()->site->events->count() }}</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-semibold mb-2">Site Units</h3>
                            <p class="text-3xl font-bold text-green-600">{{ auth()->user()->site->units->count() }}</p>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-semibold mb-2">Active Tenants</h3>
                            <p class="text-3xl font-bold text-purple-600">{{ auth()->user()->site->tenants->where('active', true)->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4">Site Management</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <a href="{{ route('event.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                                Manage Events
                            </a>
                            <a href="{{ route('unit.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center">
                                Manage Units
                            </a>
                            <a href="{{ route('question.index') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center">
                                Manage Questions
                            </a>
                            <a href="#" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-center">
                                Start Voting
                            </a>
                        </div>
                    </div>
                </div>

            @else
                <!-- Tenant Dashboard -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-2">Welcome, {{ auth()->user()->name }}</h3>
                        <p class="text-gray-600">Unit: {{ auth()->user()->unit_code ?? 'Not assigned' }}</p>
                        <p class="text-gray-600">Site: {{ auth()->user()->site->name ?? 'Not assigned' }}</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4">Available Voting Sessions</h3>
                        @if(auth()->user()->site && auth()->user()->site->events->where('date', '>=', now())->count() > 0)
                            <div class="space-y-4">
                                @foreach(auth()->user()->site->events->where('date', '>=', now())->take(5) as $event)
                                    <div class="border rounded-lg p-4">
                                        <h4 class="font-semibold">{{ $event->name }}</h4>
                                        <p class="text-gray-600">Date: {{ $event->date->format('M d, Y') }}</p>
                                        <div class="mt-2">
                                            <a href="#" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm">
                                                Join Voting
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-600">No upcoming voting sessions available.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
