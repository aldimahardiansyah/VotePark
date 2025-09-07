<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Events Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Events View -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Events</h3>
                        <div class="flex items-center space-x-4">
                            <!-- View Toggle -->
                            <div class="flex items-center space-x-2">
                                <button onclick="switchView('table')" id="table-view-btn" 
                                        class="view-toggle-btn bg-blue-500 text-white px-3 py-1 rounded text-sm transition duration-300">
                                    Table
                                </button>
                                <button onclick="switchView('cards')" id="cards-view-btn" 
                                        class="view-toggle-btn bg-gray-300 text-gray-700 px-3 py-1 rounded text-sm transition duration-300">
                                    Cards
                                </button>
                            </div>
                            @can('create', App\Models\Event::class)
                                <a href="{{ route('event.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                                    Create Event
                                </a>
                            @endcan
                        </div>
                    </div>

                    <!-- Table View -->
                    <div id="table-view" class="view-content">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            #
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Event Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Site
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Attendance
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($events as $event)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $event->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ $event->date->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ $event->site->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200">
                                                    {{ $event->units->count() }} attendees
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                <div class="flex items-center justify-center space-x-2">
                                                    <a href="{{ route('event.show', $event->id) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                                        View
                                                    </a>
                                                    @can('update', $event)
                                                        <button onclick="openImportModal({{ $event->id }})" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                                            Import Attendance
                                                        </button>
                                                    @endcan
                                                    @if(auth()->user()->isAdminSite() || auth()->user()->isSuperAdmin())
                                                        <a href="{{ route('voting.manage', $event) }}" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300">
                                                            Manage Voting
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-300">
                                                No events found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Cards View -->
                    <div id="cards-view" class="view-content hidden">
                        @forelse ($events as $event)
                            @if ($loop->first || $loop->iteration % 3 == 1)
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 @if(!$loop->first) mt-6 @endif">
                            @endif
                            <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-700 dark:to-gray-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 p-6 border border-gray-200 dark:border-gray-600">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-1">
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ $event->name }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ $event->date->format('M d, Y') }}
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            {{ $event->site->name ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200">
                                        {{ $event->units->count() }} attendees
                                    </span>
                                </div>
                                
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('event.show', $event->id) }}" 
                                       class="bg-blue-500 hover:bg-blue-700 text-white text-xs font-bold py-1 px-3 rounded transition duration-300">
                                        View Details
                                    </a>
                                    @can('update', $event)
                                        <button onclick="openImportModal({{ $event->id }})" 
                                                class="bg-green-500 hover:bg-green-700 text-white text-xs font-bold py-1 px-3 rounded transition duration-300">
                                            Import
                                        </button>
                                    @endcan
                                    @if(auth()->user()->isAdminSite() || auth()->user()->isSuperAdmin())
                                        <a href="{{ route('voting.manage', $event) }}" 
                                           class="bg-purple-500 hover:bg-purple-700 text-white text-xs font-bold py-1 px-3 rounded transition duration-300">
                                            Voting
                                        </a>
                                    @endif
                                </div>
                            </div>
                            @if ($loop->last || $loop->iteration % 3 == 0)
                                </div>
                            @endif
                        @empty
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No events</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new event.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Attendance Modal -->
    <div id="importAttendanceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Import Attendance</h3>
                <form id="importForm" action="{{ route('event.import-attendance') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="eventId" name="event_id" value="">
                    
                    <div class="mb-4">
                        <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Excel File</label>
                        <input type="file" name="file" class="block w-full text-sm text-gray-500 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept=".xlsx,.xls" required>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-4">
                        <button type="button" onclick="closeImportModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                            Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openImportModal(eventId) {
            document.getElementById('eventId').value = eventId;
            document.getElementById('importAttendanceModal').classList.remove('hidden');
        }

        function closeImportModal() {
            document.getElementById('importAttendanceModal').classList.add('hidden');
        }

        function switchView(viewType) {
            // Hide all views
            document.querySelectorAll('.view-content').forEach(view => {
                view.classList.add('hidden');
            });
            
            // Reset all button styles
            document.querySelectorAll('.view-toggle-btn').forEach(btn => {
                btn.classList.remove('bg-blue-500', 'text-white');
                btn.classList.add('bg-gray-300', 'text-gray-700');
            });
            
            // Show selected view
            document.getElementById(viewType + '-view').classList.remove('hidden');
            
            // Highlight selected button
            const activeBtn = document.getElementById(viewType + '-view-btn');
            activeBtn.classList.remove('bg-gray-300', 'text-gray-700');
            activeBtn.classList.add('bg-blue-500', 'text-white');
            
            // Save preference
            localStorage.setItem('eventsViewPreference', viewType);
        }

        // Close modal when clicking outside
        document.getElementById('importAttendanceModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImportModal();
            }
        });

        // Load saved view preference
        document.addEventListener('DOMContentLoaded', function() {
            const savedView = localStorage.getItem('eventsViewPreference') || 'table';
            switchView(savedView);
        });
    </script>
</x-app-layout>