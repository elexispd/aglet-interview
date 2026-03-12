<?php

use Livewire\Volt\Component;
use App\Services\TMDBService;

new class extends Component {
    public ?array $details = null;
    public ?string $error = null;
    public int $tmdbId = 0;

    public function loadDetails(int $tmdbId): void
    {
        $this->tmdbId = $tmdbId;
        $this->details = null;
        $this->error = null;

        if ($this->tmdbId <= 0) {
            $this->error = 'Invalid movie id.';
            $this->dispatch('movie-details-loaded');
            return;
        }

        $result = app(TMDBService::class)->getMovieDetails($this->tmdbId);

        $error = is_array($result) ? ($result['error'] ?? null) : 'Unable to load movie details.';
        $movie = is_array($result) ? ($result['movie'] ?? null) : null;

        if ($error) {
            $this->error = $error;
            $this->details = null;
        } elseif (is_array($movie) && $movie !== []) {
            $this->details = $movie;
            $this->error = null;
        } else {
            $this->details = null;
            $this->error = 'Unable to load movie details.';
        }

        $this->dispatch('movie-details-loaded');
    }
};
?>

<div 
    x-data="{ show: false, loading: false }"
    x-show="show"
    @open-movie-modal.window="show = true; loading = true; $wire.loadDetails($event.detail.tmdbId)"
    @movie-details-loaded.window="loading = false"
    @keydown.escape.window="show = false; loading = false"
    style="display: none;"
    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
>
    <div 
        class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity"
        @click="show = false; loading = false"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    <div class="relative w-full max-w-4xl bg-gray-900 border border-gray-700 rounded-xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col md:flex-row"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-90"
    >
        <button type="button" @click="show = false; loading = false" class="absolute top-3 right-3 z-20 bg-black/50 hover:bg-black/70 text-white rounded-full p-2 transition">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div x-show="loading" class="absolute inset-0 z-10 bg-gray-900 flex flex-col items-center justify-center text-gray-400">
            <svg class="w-12 h-12 mb-4 animate-spin text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="animate-pulse">Loading details...</p>
        </div>

        <div x-show="!loading" class="contents">
            @if($error)
                <div class="p-12 text-center text-gray-400 w-full flex flex-col items-center justify-center">
                    <svg class="w-12 h-12 mx-auto mb-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="text-lg font-medium text-white mb-2">Error Loading Details</p>
                    <p>{{ $error }}</p>
                    <button @click="show = false; loading = false" class="mt-6 px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded text-white transition">Close</button>
                </div>
            @elseif($details)
                <div class="md:w-1/3 bg-gray-950 relative">
                    @if(!empty($details['poster_path']))
                        <img src="https://image.tmdb.org/t/p/w500{{ $details['poster_path'] }}" alt="{{ $details['title'] }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full min-h-[300px] flex items-center justify-center text-gray-500">
                            <span class="text-lg">No Poster</span>
                        </div>
                    @endif
                </div>

                <div class="md:w-2/3 p-6 md:p-8 overflow-y-auto">
                    <h2 class="text-3xl font-bold text-white mb-2 leading-tight">
                        {{ $details['title'] }}
                    </h2>

                    @if(!empty($details['tagline']))
                        <p class="text-gray-400 italic mb-4">{{ $details['tagline'] }}</p>
                    @endif

                    <div class="flex flex-wrap gap-3 mb-6 text-sm">
                        @if(!empty($details['release_date']))
                            <span class="px-3 py-1 bg-gray-800 text-gray-200 rounded-full border border-gray-700">
                                {{ \Carbon\Carbon::parse($details['release_date'])->format('Y') }}
                            </span>
                        @endif

                        @if(!empty($details['runtime']))
                            <span class="px-3 py-1 bg-gray-800 text-gray-200 rounded-full border border-gray-700">
                                {{ $details['runtime'] }} min
                            </span>
                        @endif

                        @if(!empty($details['vote_average']))
                            <span class="px-3 py-1 bg-gray-800 text-yellow-400 font-bold rounded-full border border-gray-700 flex items-center gap-1">
                                <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                {{ number_format($details['vote_average'], 1) }}
                            </span>
                        @endif
                    </div>

                    @if(!empty($details['genres']))
                        <div class="flex flex-wrap gap-2 mb-6">
                            @foreach($details['genres'] as $genre)
                                <span class="text-xs font-semibold px-2 py-1 bg-red-900/30 text-red-200 rounded border border-red-900/50">
                                    {{ $genre }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <div class="prose prose-invert max-w-none mb-8">
                        <h3 class="text-lg font-semibold text-white mb-2">Overview</h3>
                        <p class="text-gray-300 leading-relaxed">
                            {{ $details['overview'] ?: 'No overview available for this movie.' }}
                        </p>
                    </div>

                    <div class="flex items-center gap-4 mt-auto">
                        <a href="https://www.themoviedb.org/movie/{{ $tmdbId }}" target="_blank" class="flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition shadow-lg shadow-red-900/20">
                            <span>View on TMDB</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                        
                        <button @click="show = false; loading = false" class="px-5 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 font-medium rounded-lg transition border border-gray-700">
                            Close
                        </button>
                    </div>
                </div>
            @else
                <div class="p-12 text-center text-gray-400 w-full flex flex-col items-center justify-center">
                    <p class="text-lg font-medium text-white mb-2">No Details</p>
                    <button @click="show = false; loading = false" class="mt-6 px-4 py-2 bg-gray-800 hover:bg-gray-700 rounded text-white transition">Close</button>
                </div>
            @endif
        </div>
    </div>
</div>
