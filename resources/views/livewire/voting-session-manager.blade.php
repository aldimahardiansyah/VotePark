<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Session Status Messages -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- Create/Manage Voting Session -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Voting Session for: {{ $event->name }}</h3>
            
            @if (!$votingSession)
                <!-- Create New Session -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                    <h4 class="text-md font-medium mb-4">Create New Voting Session</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Session Name</label>
                            <input type="text" wire:model="sessionName" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('sessionName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Voting Method</label>
                            <select wire:model="votingMethod" 
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="one_man_one_vote">One Man One Vote</option>
                                <option value="npp_based">NPP Based</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                        <textarea wire:model="sessionDescription" rows="3"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <button wire:click="createSession" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Create Voting Session
                    </button>
                </div>
            @else
                <!-- Session Management -->
                <div class="border rounded-lg p-4 mb-4 
                    @if($votingSession->status === 'active') bg-green-50 border-green-200
                    @elseif($votingSession->status === 'paused') bg-yellow-50 border-yellow-200
                    @elseif($votingSession->status === 'completed') bg-gray-50 border-gray-200
                    @else bg-blue-50 border-blue-200 @endif">
                    
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="text-lg font-medium">{{ $votingSession->name }}</h4>
                        <span class="px-3 py-1 text-sm font-medium rounded-full
                            @if($votingSession->status === 'active') bg-green-100 text-green-800
                            @elseif($votingSession->status === 'paused') bg-yellow-100 text-yellow-800
                            @elseif($votingSession->status === 'completed') bg-gray-100 text-gray-800
                            @else bg-blue-100 text-blue-800 @endif">
                            {{ ucfirst(str_replace('_', ' ', $votingSession->status)) }}
                        </span>
                    </div>
                    
                    @if($votingSession->description)
                        <p class="text-gray-600 mb-4">{{ $votingSession->description }}</p>
                    @endif
                    
                    <div class="flex flex-wrap gap-2">
                        @if($votingSession->isDraft())
                            <button wire:click="startSession" 
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Start Session
                            </button>
                        @endif
                        
                        @if($votingSession->isActive())
                            <button wire:click="pauseSession" 
                                    class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                Pause Session
                            </button>
                            <button wire:click="endSession" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                End Session
                            </button>
                        @endif
                        
                        @if($votingSession->isPaused())
                            <button wire:click="resumeSession" 
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Resume Session
                            </button>
                            <button wire:click="endSession" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                End Session
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($votingSession)
        <!-- Add Questions Section -->
        @if($votingSession->isDraft() || $votingSession->allow_question_changes)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h4 class="text-lg font-semibold mb-4">Add New Question</h4>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Question Text</label>
                        <textarea wire:model="newQuestionText" rows="3" placeholder="Enter your question here..."
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        @error('newQuestionText') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Answer Options</label>
                        @foreach($newAnswers as $index => $answer)
                            <div class="flex items-center mb-2">
                                <input type="text" wire:model="newAnswers.{{ $index }}" 
                                       placeholder="Answer option {{ $index + 1 }}"
                                       class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @if(count($newAnswers) > 2)
                                    <button wire:click="removeAnswerField({{ $index }})" 
                                            class="ml-2 text-red-500 hover:text-red-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                            @error('newAnswers.' . $index) <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        @endforeach
                        
                        <button wire:click="addAnswerField" 
                                class="text-blue-500 hover:text-blue-700 text-sm">
                            + Add Another Option
                        </button>
                    </div>
                    
                    <button wire:click="addQuestion" 
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Add Question
                    </button>
                </div>
            </div>
        @endif

        <!-- Questions List -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-lg font-semibold">Questions ({{ count($questions) }})</h4>
                    @if($votingSession->isActive() && $currentQuestionId)
                        <button wire:click="nextQuestion" 
                                class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                            Next Question →
                        </button>
                    @endif
                </div>
                
                @if(empty($questions))
                    <div class="text-center py-8 text-gray-500">
                        <p>No questions added yet. Add your first question above.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($questions as $question)
                            <div class="border rounded-lg p-4 
                                @if($question['id'] == $currentQuestionId) border-blue-500 bg-blue-50
                                @elseif($question['status'] === 'completed') border-gray-300 bg-gray-50
                                @else border-gray-200 @endif">
                                
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-sm font-medium text-gray-600">Q{{ $question['order'] }}</span>
                                            @if($question['id'] == $currentQuestionId)
                                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                                    Active
                                                </span>
                                            @elseif($question['status'] === 'completed')
                                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                                    Completed
                                                </span>
                                            @endif
                                        </div>
                                        <h5 class="font-medium text-gray-900">{{ $question['question'] }}</h5>
                                    </div>
                                    
                                    @if($votingSession->isActive() && $question['id'] != $currentQuestionId && $question['status'] !== 'completed')
                                        <button wire:click="activateQuestion({{ $question['id'] }})" 
                                                class="bg-green-500 hover:bg-green-700 text-white text-sm font-bold py-1 px-3 rounded">
                                            Activate
                                        </button>
                                    @endif
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-3">
                                    @foreach($question['answers'] as $answer)
                                        <div class="bg-white border rounded px-3 py-2">
                                            <span class="text-sm">{{ $answer['answer'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                
                                @if($question['id'] == $currentQuestionId)
                                    <div class="mt-3 pt-3 border-t">
                                        <p class="text-sm text-blue-600 font-medium">This question is currently active for voting</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
