<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('devlogger.dashboard') }}" 
                       class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Dashboard
                    </a>
                    
                    @php
                        $levelColors = [
                            'emergency' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                            'alert' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                            'critical' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                            'error' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                            'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                            'notice' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                            'info' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                            'debug' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
                        ];
                    @endphp
                    
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $levelColors[$log->level] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' }}">
                        {{ ucfirst($log->level) }}
                    </span>
                    
                    @if($log->status === 'resolved')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Resolved
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Open
                        </span>
                    @endif
                </div>
                
                <div class="flex items-center space-x-3">
                    @if($log->status === 'open')
                        <button wire:click="markResolved" 
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span wire:loading.remove wire:target="markResolved">Mark Resolved</span>
                            <span wire:loading wire:target="markResolved">Processing...</span>
                        </button>
                    @else
                        <button wire:click="markOpen" 
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="markOpen">Reopen</span>
                            <span wire:loading wire:target="markOpen">Processing...</span>
                        </button>
                    @endif
                    
                    <button wire:click="deleteLog" 
                            wire:confirm="Are you sure you want to delete this log?"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        <span wire:loading.remove wire:target="deleteLog">Delete</span>
                        <span wire:loading wire:target="deleteLog">Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="px-6 py-4">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ $log->log ?? $log->message ?? 'No message' }}</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ \Carbon\Carbon::parse($log->created_at)->format('F j, Y \a\t g:i A') }}
                @if($log->updated_at && $log->updated_at !== $log->created_at)
                    • Updated {{ \Carbon\Carbon::parse($log->updated_at)->format('F j, Y \a\t g:i A') }}
                @endif
            </div>
        </div>
    </div>

    <!-- File Information -->
    @if($log->file_path)
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">File Information</h2>
            </div>
            <div class="px-6 py-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900 dark:text-white font-mono">
                            {{ $log->file_path }}
                            @if($log->line_number)
                                <span class="text-gray-500 dark:text-gray-400">:{{ $log->line_number }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 ml-4">
                        <button wire:click="copyFilePath" 
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50"
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="copyFilePath">Copy</span>
                            <span wire:loading wire:target="copyFilePath">...</span>
                        </button>
                        <button wire:click="openInIde" 
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            <span wire:loading.remove wire:target="openInIde">Open in IDE</span>
                            <span wire:loading wire:target="openInIde">...</span>
                        </button>
                    </div>
                </div>

                <!-- File Preview -->
                @if($filePreview)
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 overflow-x-auto">
                        <div class="code-preview">
                            @foreach($filePreview['lines'] as $line)
                                <div class="flex {{ $line['is_target'] ? 'line-highlight' : '' }}">
                                    <div class="w-12 text-right text-gray-400 dark:text-gray-500 text-xs pr-4 select-none">
                                        {{ $line['number'] }}
                                    </div>
                                    <div class="flex-1 text-gray-900 dark:text-gray-100">
                                        {{ $line['content'] ?: ' ' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Queue Information -->
    @if($log->queue)
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Queue Information</h2>
            </div>
            <div class="px-6 py-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Queue</label>
                    <div class="text-sm font-mono text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded">
                        {{ $log->queue }}
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Exception Details -->
    @if($log->exception_class)
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Exception Details</h2>
            </div>
            <div class="px-6 py-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Exception Class</label>
                    <div class="text-sm font-mono text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded">
                        {{ $log->exception_class }}
                    </div>
                </div>

                @if($log->stack_trace)
                    <div>
                        <button wire:click="toggleStackTrace" 
                                class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors duration-200">
                            <svg class="w-4 h-4 mr-1 transform transition-transform duration-200 {{ $showStackTrace ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            Stack Trace
                        </button>
                        
                        @if($showStackTrace)
                            <div class="mt-2 bg-gray-50 dark:bg-gray-900 rounded-lg p-4 overflow-x-auto">
                                <pre class="text-xs text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $log->stack_trace }}</pre>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Request Information -->
    @if($log->request_url || $log->request_method || $log->user_agent || $log->ip_address)
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Request Information</h2>
            </div>
            <div class="px-6 py-4 space-y-4">
                @if($log->request_method && $log->request_url)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Request</label>
                        <div class="text-sm font-mono text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded">
                            <span class="font-bold">{{ $log->request_method }}</span> {{ $log->request_url }}
                        </div>
                    </div>
                @endif

                @if($log->ip_address)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">IP Address</label>
                        <div class="text-sm font-mono text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded">
                            {{ $log->ip_address }}
                        </div>
                    </div>
                @endif

                @if($log->user_agent)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User Agent</label>
                        <div class="text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded break-all">
                            {{ $log->user_agent }}
                        </div>
                    </div>
                @endif

                @if($log->user_id)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User ID</label>
                        <div class="text-sm font-mono text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-900 px-3 py-2 rounded">
                            {{ $log->user_id }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Tags -->
    @if($log->tags)
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Tags</h2>
            </div>
            <div class="px-6 py-4">
                <div class="flex flex-wrap gap-2">
                    @foreach($log->tags as $tag)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ $tag }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Context Data -->
    @if($log->context)
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <button wire:click="toggleContext" 
                        class="flex items-center text-lg font-medium text-gray-900 dark:text-white hover:text-gray-700 dark:hover:text-gray-300 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2 transform transition-transform duration-200 {{ $showContext ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    Context Data
                </button>
            </div>
            
            @if($showContext)
                <div class="px-6 py-4">
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 overflow-x-auto">
                        <pre class="text-sm text-gray-900 dark:text-gray-100">{{ json_encode($log->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>