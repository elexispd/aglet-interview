<?php

use Livewire\Volt\Component;
use App\Services\TMDBService;

new class extends Component {
    public $query = '';
    public $results = [];

    public function updatedQuery(TMDBService $tmdb)
    {
        if (strlen($this->query) >= 2) {
            $this->results = $tmdb->searchMovies($this->query)->toArray();
        } else {
            $this->results = [];
        }
    }
};
?>

<div class="relative w-full md:w-auto" x-data="{ open: false }" @click.away="open = false">
    <div class="relative">
        <input
            wire:model.live.debounce.300ms="query"
            @focus="open = true"
            @input="open = true"
            type="text"
            placeholder="Search movies..."
            class="bg-gray-700 text-white pl-10 pr-4 py-2 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500 w-full md:w-64 transition-all"
        >
        <div class="absolute left-3 top-2.5 text-gray-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
    </div>

    @if(count($results) > 0 && !empty($query))
        <div
            class="absolute top-full right-0 w-full md:w-80 bg-gray-800 mt-2 rounded-lg shadow-xl z-50 overflow-hidden border border-gray-700"
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            style="display: none;"
        >
            @foreach($results as $result)
                <a href="https://www.themoviedb.org/movie/{{ $result['id'] }}" target="_blank" class="flex items-center p-3 hover:bg-gray-700 cursor-pointer transition border-b border-gray-700 last:border-0 group block">
                    @if(isset($result['poster_path']))
                        <img src="https://image.tmdb.org/t/p/w92{{ $result['poster_path'] }}" class="w-10 h-14 object-cover rounded mr-3 shadow-sm group-hover:shadow-md transition">
                    @else
                        <div class="w-10 h-14 bg-gray-600 rounded mr-3 flex items-center justify-center text-xs text-gray-400">No Img</div>
                    @endif
                    <div>
                        <h4 class="text-white text-sm font-bold group-hover:text-red-400 transition">{{ $result['title'] }}</h4>
                        <p class="text-gray-400 text-xs">{{ isset($result['release_date']) ? substr($result['release_date'], 0, 4) : 'N/A' }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @elseif(!empty($query) && strlen($query) >= 2)
        <div
            class="absolute top-full right-0 w-full md:w-64 bg-gray-800 mt-2 rounded-lg shadow-xl z-50 p-4 border border-gray-700 text-center text-gray-400 text-sm"
            x-show="open"
            style="display: none;"
        >
            No results found.
        </div>
    @endif
</div>
