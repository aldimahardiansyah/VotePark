<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Voting Session') }}
            @if($selectedEvent)
                - {{ $selectedEvent->name }}
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($selectedEvent)
                <!-- Voting Session Manager Component -->
                <livewire:voting-session-manager :event="$selectedEvent" />
            @else
                <!-- Event Selection -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Select an Event</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-6">Choose an event to create a voting session for:</p>
                        
                        <form action="{{ route('question.create') }}" method="GET" class="space-y-4">
                            <div>
                                <label for="event_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Event
                                </label>
                                <select name="event_id" id="event_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm" required>
                                    <option value="">Choose an event...</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}">
                                            {{ $event->name }} - {{ $event->date->format('M d, Y') }}
                                            @if($event->site)
                                                ({{ $event->site->name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <a href="{{ route('question.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                                    Back to Sessions
                                </a>
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                                    Continue
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>