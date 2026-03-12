<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TMDBService
{
    protected $apiKey;

    protected $baseUrl = 'https://api.themoviedb.org/3';

    protected $popularCacheKey = 'tmdb_movies_popular_45_v2';

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.key', env('TMDB_API_KEY'));
    }

    /**
     * Get paginated movies (9 per page, total 45 items).
     */
    public function getMovies(int $page = 1): Collection
    {
        // Get the full dataset (45 movies), memoized for the request
        $movies = $this->getAllMovies();

        // Chunk into pages of 9
        $chunks = $movies->chunk(9);

        // Get the chunk for the requested page (1-based index)
        $chunk = $chunks->values()->get($page - 1);

        return $chunk ? collect($chunk) : collect([]);
    }

    /**
     * Get all 45 movies, cached and memoized.
     */
    protected function getAllMovies(): Collection
    {
        return once(function () {
            $cached = Cache::get($this->popularCacheKey);
            if ($cached instanceof Collection) {
                return $cached;
            }
            if (is_array($cached)) {
                return collect($cached);
            }

            if (empty($this->apiKey)) {
                return collect();
            }

            $results = [];
            for ($i = 1; $i <= 3; $i++) {
                $response = Http::retry(2, 200)
                    ->timeout(10)
                    ->get("{$this->baseUrl}/movie/popular", [
                        'api_key' => $this->apiKey,
                        'page' => $i,
                        'language' => 'en-US',
                        'include_adult' => false,
                    ]);

                if ($response->successful()) {
                    $results = array_merge($results, $response->json()['results'] ?? []);
                }
            }

            $movies = collect($results)->take(45)->values();
            if ($movies->isEmpty()) {
                return collect();
            }

            Cache::put($this->popularCacheKey, $movies, 3600);

            return $movies;
        });
    }

    /**
     * Search movies for the autocomplete dropdown.
     */
    public function searchMovies(string $query): Collection
    {
        if (empty($query)) {
            return collect([]);
        }

        return Cache::remember('tmdb_search_'.md5($query), 3600, function () use ($query) {
            $response = Http::get("{$this->baseUrl}/search/movie", [
                'api_key' => $this->apiKey,
                'query' => $query,
                'include_adult' => false,
            ]);

            if ($response->successful()) {
                return collect($response->json()['results'] ?? [])->take(5); // Limit for autocomplete
            }

            return collect([]);
        });
    }
}
