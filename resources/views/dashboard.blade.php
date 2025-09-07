<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }} - {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdminSite())
                <!-- Admin Dashboard with Tabs -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Tab Navigation -->
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button onclick="switchTab('dashboard')" 
                                    class="tab-btn border-indigo-500 text-indigo-600 dark:text-indigo-400 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" 
                                    id="tab-dashboard">
                                Dashboard
                            </button>
                            <button onclick="switchTab('events')" 
                                    class="tab-btn border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" 
                                    id="tab-events">
                                Events
                            </button>
                            <button onclick="switchTab('units')" 
                                    class="tab-btn border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" 
                                    id="tab-units">
                                Units
                            </button>
                        </nav>
                    </div>

                    <!-- Dashboard Tab Content -->
                    <div id="content-dashboard" class="tab-content p-6">
                        @if(auth()->user()->isSuperAdmin())
                            <!-- Super Admin Dashboard -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 overflow-hidden shadow-sm sm:rounded-lg">
                                    <div class="p-6 text-white">
                                        <h3 class="text-lg font-semibold mb-2">Total Sites</h3>
                                        <p class="text-3xl font-bold">{{ \App\Models\Site::count() }}</p>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-green-500 to-green-600 overflow-hidden shadow-sm sm:rounded-lg">
                                    <div class="p-6 text-white">
                                        <h3 class="text-lg font-semibold mb-2">Total Users</h3>
                                        <p class="text-3xl font-bold">{{ \App\Models\User::count() }}</p>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-purple-500 to-purple-600 overflow-hidden shadow-sm sm:rounded-lg">
                                    <div class="p-6 text-white">
                                        <h3 class="text-lg font-semibold mb-2">Total Events</h3>
                                        <p class="text-3xl font-bold">{{ \App\Models\Event::count() }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Quick Actions</h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <a href="{{ route('event.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center transition duration-300">
                                        Manage Events
                                    </a>
                                    <a href="{{ route('unit.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center transition duration-300">
                                        Manage Units
                                    </a>
                                    <a href="{{ route('question.index') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center transition duration-300">
                                        Manage Questions
                                    </a>
                                    <a href="#" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-center transition duration-300">
                                        System Settings
                                    </a>
                                </div>
                            </div>

                        @else
                            <!-- Site Admin Dashboard -->
                            <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                                <div class="p-6 text-white">
                                    <h3 class="text-lg font-semibold mb-2">{{ auth()->user()->site->name }}</h3>
                                    <p class="opacity-90">{{ auth()->user()->site->description }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 overflow-hidden shadow-sm sm:rounded-lg">
                                    <div class="p-6 text-white">
                                        <h3 class="text-lg font-semibold mb-2">Site Events</h3>
                                        <p class="text-3xl font-bold">{{ auth()->user()->site->events->count() }}</p>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-green-500 to-green-600 overflow-hidden shadow-sm sm:rounded-lg">
                                    <div class="p-6 text-white">
                                        <h3 class="text-lg font-semibold mb-2">Site Units</h3>
                                        <p class="text-3xl font-bold">{{ auth()->user()->site->units->count() }}</p>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-r from-purple-500 to-purple-600 overflow-hidden shadow-sm sm:rounded-lg">
                                    <div class="p-6 text-white">
                                        <h3 class="text-lg font-semibold mb-2">Active Tenants</h3>
                                        <p class="text-3xl font-bold">{{ \App\Models\User::where('role', 'tenant')->where('site_id', auth()->user()->site_id)->where('active', true)->count() }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">Site Management</h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <a href="{{ route('event.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center transition duration-300">
                                        Manage Events
                                    </a>
                                    <a href="{{ route('unit.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center transition duration-300">
                                        Manage Units
                                    </a>
                                    <a href="{{ route('question.index') }}" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center transition duration-300">
                                        Manage Questions
                                    </a>
                                    @if(auth()->user()->site && auth()->user()->site->events->count() > 0)
                                        <a href="{{ route('voting.manage', auth()->user()->site->events->first()) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-center transition duration-300">
                                            Start Voting
                                        </a>
                                    @else
                                        <span class="bg-gray-400 text-white font-bold py-2 px-4 rounded text-center cursor-not-allowed">
                                            Start Voting
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Events Tab Content -->
                    <div id="content-events" class="tab-content hidden p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Events</h3>
                            <a href="{{ route('event.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                                View All Events
                            </a>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @php
                                $recentEvents = auth()->user()->isSuperAdmin() 
                                    ? \App\Models\Event::latest()->take(6)->get()
                                    : \App\Models\Event::where('site_id', auth()->user()->site_id)->latest()->take(6)->get();
                            @endphp
                            @forelse($recentEvents as $event)
                                <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6">
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $event->name }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $event->date->format('M d, Y') }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $event->units->count() }} participants</p>
                                    <a href="{{ route('event.show', $event) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm transition duration-300">
                                        View Details
                                    </a>
                                </div>
                            @empty
                                <div class="col-span-3 text-center py-8">
                                    <p class="text-gray-500 dark:text-gray-400">No events found.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Units Tab Content -->
                    <div id="content-units" class="tab-content hidden p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Units</h3>
                            <a href="{{ route('unit.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                                View All Units
                            </a>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @php
                                $recentUnits = auth()->user()->isSuperAdmin() 
                                    ? \App\Models\Unit::with('users')->latest()->take(6)->get()
                                    : \App\Models\Unit::with('users')->where('site_id', auth()->user()->site_id)->latest()->take(6)->get();
                            @endphp
                            @forelse($recentUnits as $unit)
                                <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-6">
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $unit->code }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $unit->tower }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Owner: {{ $unit->users->first()->name ?? 'No owner' }}</p>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">NPP: {{ number_format($unit->npp, 2) }}</span>
                                        <span class="text-gray-600 dark:text-gray-400">{{ number_format($unit->wide, 2) }} m²</span>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-3 text-center py-8">
                                    <p class="text-gray-500 dark:text-gray-400">No units found.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            @else
                <!-- Tenant Dashboard -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-2">Welcome, {{ auth()->user()->name }}</h3>
                        <p class="text-gray-600 dark:text-gray-400">Site: {{ auth()->user()->site->name ?? 'Not assigned' }}</p>
                        @if(auth()->user()->units->count() > 0)
                            <p class="text-gray-600 dark:text-gray-400">Your Units: {{ auth()->user()->units->pluck('code')->join(', ') }}</p>
                        @else
                            <p class="text-gray-600 dark:text-gray-400">No units assigned</p>
                        @endif
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Available Voting Sessions</h3>
                        @if(auth()->user()->site && auth()->user()->site->events->where('date', '>=', now())->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach(auth()->user()->site->events->where('date', '>=', now())->take(6) as $event)
                                    @php
                                        $canVote = auth()->user()->units->some(function($unit) use ($event) {
                                            return $event->units->contains($unit->id);
                                        });
                                    @endphp
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                        <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $event->name }}</h4>
                                        <p class="text-gray-600 dark:text-gray-400 text-sm">Date: {{ $event->date->format('M d, Y H:i') }}</p>
                                        <p class="text-gray-600 dark:text-gray-400 text-sm">Participants: {{ $event->units->count() }}</p>
                                        <div class="mt-3">
                                            @if($canVote)
                                                <a href="{{ route('voting.vote', $event) }}" 
                                                   class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm transition duration-300">
                                                    Join Voting
                                                </a>
                                            @else
                                                <span class="bg-gray-400 text-white font-bold py-2 px-4 rounded text-sm cursor-not-allowed">
                                                    Not Eligible
                                                </span>
                                            @endif
                                            <a href="{{ route('voting.display', $event) }}" 
                                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm ml-2 transition duration-300">
                                                View Results
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <p class="text-gray-600 dark:text-gray-400">No upcoming voting sessions available.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.add('hidden'));
            
            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-btn');
            tabButtons.forEach(btn => {
                btn.classList.remove('border-indigo-500', 'text-indigo-600', 'dark:text-indigo-400');
                btn.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            });
            
            // Show selected tab content
            document.getElementById('content-' + tabName).classList.remove('hidden');
            
            // Add active class to selected tab button
            const activeTab = document.getElementById('tab-' + tabName);
            activeTab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            activeTab.classList.add('border-indigo-500', 'text-indigo-600', 'dark:text-indigo-400');
        }
    </script>
</x-app-layout>
