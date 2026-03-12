<?php

use Livewire\Volt\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    #[On('favorites-updated')]
    public function refreshFavorites(): void
    {
    }

    public function with()
    {
        return [
            'favorites' => Auth::check() ? Auth::user()->favorites()->orderByDesc('created_at')->get() : collect([]),
        ];
    }
};
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8 text-white border-b border-gray-700 pb-4">My Favorites</h1>

    @if($favorites->isEmpty())
        <div class="text-center py-12">
            <p class="text-gray-400 text-lg mb-4">You haven't saved any movies yet.</p>
            <a href="/" class="text-red-500 hover:text-red-400 font-bold underline">Browse Movies</a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($favorites as $favorite)
                <!-- Map favorite model to array structure expected by movie-card if needed, or update movie-card to handle model -->
                <!-- Assuming movie-card handles array access on model or we pass array -->
                <livewire:movie-card :movie="$favorite" :key="$favorite->id" />
            @endforeach
        </div>
    @endif
</div>
