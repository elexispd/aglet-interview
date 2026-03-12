<?php

use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new class extends Component {
    #[Url]
    public int $page = 1;

    public array $movies = [];
    public ?string $error = null;

    public function mount(): void
    {
        $this->loadMovies();
    }

    public function updatedPage(): void
    {
        $this->loadMovies();
    }

    public function loadMovies(): void
    {
        $result = app(\App\Services\TMDBService::class)->getMoviesPage((int) $this->page);
        $this->movies = ($result['movies'] ?? collect())->values()->all();
        $this->error = $result['error'] ?? null;
    }

    public function nextPage(): void
    {
        if ($this->page < 5) {
            $this->page++;
            $this->loadMovies();
        }
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
            $this->loadMovies();
        }
    }
};
?>

<div class="container mx-auto px-4 py-8">
    @if(empty($movies))
        <div class="text-center py-16">
            <div class="text-gray-400 mb-4">{{ $error ?? 'No movies loaded.' }}</div>
            <button wire:click="loadMovies" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                Reload
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($movies as $movie)
                <livewire:movie-card :movie="$movie" :key="$movie['id']" />
            @endforeach
        </div>
    @endif

    <div class="flex justify-between mt-8 items-center">
        <button wire:click="previousPage" @disabled($page <= 1) class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
            Previous
        </button>
        <span class="text-white font-medium">Page {{ $page }} of 5</span>
        <button wire:click="nextPage" @disabled($page >= 5) class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
            Next
        </button>
    </div>
</div>
