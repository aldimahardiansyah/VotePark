<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Voting Sessions Management') }}
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

            <!-- Create New Voting Session -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Create New Voting Session</h3>
                    </div>
                    
                    <form action="{{ route('question.create') }}" method="GET" class="flex items-end space-x-4">
                        <div class="flex-1">
                            <label for="event_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Select Event
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
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-300">
                            Create Voting Session
                        </button>
                    </form>
                </div>
            </div>

            <!-- Existing Voting Sessions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-6">Existing Voting Sessions</h3>

                    @if($events->flatMap->votingSessions->count() > 0)
                        <div class="space-y-6">
                            @foreach($events as $event)
                                @if($event->votingSessions->count() > 0)
                                    <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                                        <h4 class="font-semibold text-lg mb-3">{{ $event->name }} - {{ $event->date->format('M d, Y') }}</h4>
                                        
                                        <div class="space-y-4">
                                            @foreach($event->votingSessions as $session)
                                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                                    <div class="flex justify-between items-start mb-3">
                                                        <div>
                                                            <h5 class="font-medium">{{ $session->name }}</h5>
                                                            @if($session->description)
                                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $session->description }}</p>
                                                            @endif
                                                            <div class="flex items-center space-x-4 mt-2">
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                                    @if($session->status === 'active') bg-green-100 text-green-800
                                                                    @elseif($session->status === 'paused') bg-yellow-100 text-yellow-800
                                                                    @elseif($session->status === 'completed') bg-gray-100 text-gray-800
                                                                    @else bg-blue-100 text-blue-800
                                                                    @endif">
                                                                    {{ ucfirst($session->status) }}
                                                                </span>
                                                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                                                    {{ $session->questions->count() }} questions
                                                                </span>
                                                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                                                    Method: {{ ucfirst(str_replace('_', ' ', $session->voting_method)) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <a href="{{ route('voting.manage', $event) }}" 
                                                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm transition duration-300">
                                                            Manage
                                                        </a>
                                                    </div>

                                                    @if($session->questions->count() > 0)
                                                        <div class="mt-3">
                                                            <h6 class="text-sm font-medium mb-2">Questions:</h6>
                                                            <div class="space-y-2">
                                                                @foreach($session->questions as $question)
                                                                    <div class="text-sm bg-white dark:bg-gray-600 rounded p-2">
                                                                        <div class="flex justify-between items-start">
                                                                            <span>{{ $loop->iteration }}. {{ $question->question }}</span>
                                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                                                @if($question->status === 'active') bg-green-100 text-green-800
                                                                                @elseif($question->status === 'completed') bg-gray-100 text-gray-800
                                                                                @else bg-blue-100 text-blue-800
                                                                                @endif">
                                                                                {{ ucfirst($question->status ?? 'draft') }}
                                                                            </span>
                                                                        </div>
                                                                        @if($question->answers->count() > 0)
                                                                            <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                                                                Answers: {{ $question->answers->pluck('answer')->join(', ') }}
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @else
                                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-3">No questions added yet.</p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No voting sessions</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new voting session.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>