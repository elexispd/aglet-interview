<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $movie;
    public $isFavorite = false;
    public $tmdbId;

    public function mount($movie)
    {
        $this->movie = $movie;
        $this->tmdbId = $movie['tmdb_id'] ?? $movie['id'];

        if (Auth::check()) {
            $this->isFavorite = Auth::user()->favorites()->where('tmdb_id', $this->tmdbId)->exists();
        }
    }

    public function toggleFavorite()
    {
        if (!Auth::check()) {
            return $this->redirect('/login');
        }

        $user = Auth::user();
        if ($this->isFavorite) {
            $user->favorites()->where('tmdb_id', $this->tmdbId)->delete();
            $this->isFavorite = false;
            $this->dispatch('favorites-updated');
        } else {
            $user->favorites()->create([
                'tmdb_id' => $this->tmdbId,
                'title' => $this->movie['title'],
                'poster_path' => $this->movie['poster_path'],
                'release_date' => $this->movie['release_date'] ?? null,
            ]);
            $this->isFavorite = true;
            $this->dispatch('favorites-updated');
        }
    }
};
?>

<div class="movie-card relative group bg-gray-900 rounded-lg overflow-hidden shadow-lg transition-transform duration-300 hover:scale-105 h-[500px]">
    @if(!empty($movie['poster_path']))
        <img src="https://image.tmdb.org/t/p/w500{{ $movie['poster_path'] }}" alt="{{ $movie['title'] }}" class="w-full h-full object-cover">
    @else
        <div class="w-full h-full bg-gray-800 flex items-center justify-center text-gray-400">
            No poster
        </div>
    @endif

    <button wire:click="toggleFavorite" class="absolute top-3 right-3 z-20 text-red-500 bg-black/50 rounded-full p-2 backdrop-blur hover:bg-black/70 transition">
        @if($isFavorite)
            <svg class="w-6 h-6 fill-current heart-pulse" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        @else
            <svg class="w-6 h-6 stroke-current fill-none hover:fill-current" viewBox="0 0 24 24" stroke-width="2"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        @endif
    </button>

    <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-6">
        <h3 class="text-xl font-bold text-white mb-2 drop-shadow-md">{{ $movie['title'] }}</h3>
        <p class="text-gray-300 text-sm mb-4 drop-shadow-md">
            {{ !empty($movie['release_date']) ? \Carbon\Carbon::parse($movie['release_date'])->format('M d, Y') : 'N/A' }}
        </p>
    </div>
</div>
