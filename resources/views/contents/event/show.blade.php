<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $event->name }} - Event Details
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Event Voting Card -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4">Voting Management</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Event Date</p>
                                    <p class="font-medium">{{ $event->date->format('M d, Y H:i') }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Site</p>
                                    <p class="font-medium">{{ $event->site->name ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Participants</p>
                                    <p class="font-medium">{{ $event->units->count() }} units</p>
                                </div>

                                @if(auth()->user()->isAdminSite() || auth()->user()->isSuperAdmin())
                                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <a href="{{ route('voting.manage', $event) }}" 
                                           class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center block transition duration-300">
                                            Manage Voting Session
                                        </a>
                                    </div>
                                @endif

                                @if(auth()->user()->isTenant() && $event->units->contains(function($unit) {
                                    return $unit->users->contains('id', auth()->id());
                                }))
                                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <a href="{{ route('voting.vote', $event) }}" 
                                           class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center block transition duration-300">
                                            Join Voting
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Participants Table -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-semibold">Event Participants</h3>
                                @can('update', $event)
                                    <button onclick="openAddParticipantModal()" 
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                                        Add Participant
                                    </button>
                                @endcan
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                No
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Owner Name
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Unit Code
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                NPP
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Email
                                            </th>
                                            @can('update', $event)
                                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Actions
                                                </th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @forelse ($event->units as $unit)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                    {{ $loop->iteration }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $unit->users->first()->name ?? 'No owner' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                    {{ $unit->code }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-right">
                                                    {{ number_format($unit->npp ?? 0, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                    {{ $unit->users->first()->email ?? '-' }}
                                                </td>
                                                @can('update', $event)
                                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                        <form action="{{ route('event.remove-participant') }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('delete')
                                                            <input type="hidden" name="event_id" value="{{ $event->id }}">
                                                            <input type="hidden" name="unit_id" value="{{ $unit->id }}">
                                                            <button type="submit" 
                                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" 
                                                                    onclick="return confirm('Are you sure you want to remove this participant?')">
                                                                Remove
                                                            </button>
                                                        </form>
                                                    </td>
                                                @endcan
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ auth()->user()->can('update', $event) ? '6' : '5' }}" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-300">
                                                    No participants found.
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
        </div>
    </div>

    <!-- Add Participant Modal -->
    @can('update', $event)
        <div id="addParticipantModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Add Participant</h3>
                    <form action="{{ route('event.add-participant') }}" method="POST">
                        @csrf
                        <input type="hidden" name="event_id" value="{{ $event->id }}">
                        
                        <div class="mb-4">
                            <label for="unit_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Unit</label>
                            <select name="unit_id" class="block w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="">Select a unit...</option>
                                @foreach(\App\Models\Unit::where('site_id', $event->site_id)->whereHas('users')->get() as $unit)
                                    @if(!$event->units->contains($unit->id))
                                        <option value="{{ $unit->id }}">{{ $unit->code }} - {{ $unit->users->first()->name ?? 'No owner' }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="flex items-center justify-end space-x-4">
                            <button type="button" onclick="closeAddParticipantModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                                Cancel
                            </button>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                                Add Participant
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    <script>
        function openAddParticipantModal() {
            document.getElementById('addParticipantModal').classList.remove('hidden');
        }

        function closeAddParticipantModal() {
            document.getElementById('addParticipantModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('addParticipantModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddParticipantModal();
            }
        });
    </script>
</x-app-layout>
