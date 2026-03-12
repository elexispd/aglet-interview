<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TMDBService
{
    protected $apiKey;

    protected $baseUrl = 'https://api.themoviedb.org/3';

    protected $popularCacheKey = 'tmdb_movies_popular_45_v2';

    public function __construct()
    {
        $this->apiKey = (string) config('services.tmdb.key', '');
    }

    /**
     * Get paginated movies (9 per page, total 45 items).
     */
    public function getMovies(int $page = 1): Collection
    {
        $movies = $this->getAllMovies();

        $chunks = $movies->chunk(9);

        $chunk = $chunks->values()->get($page - 1);

        return ($chunk ? collect($chunk) : collect([]))->map(fn ($movie) => $this->normalizeMovie($movie));
    }

    public function getMoviesPage(int $page = 1): array
    {
        if (empty($this->apiKey)) {
            return [
                'movies' => collect(),
                'error' => 'TMDB_API_KEY is missing.',
            ];
        }

        $movies = $this->getMovies($page);
        if ($movies->isEmpty()) {
            return [
                'movies' => collect(),
                'error' => 'Unable to load movies from TMDB.',
            ];
        }

        return [
            'movies' => $movies,
            'error' => null,
        ];
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
                } else {
                    Log::warning('TMDB popular request failed', [
                        'status' => $response->status(),
                        'page' => $i,
                    ]);
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
            if (empty($this->apiKey)) {
                return collect([]);
            }

            $response = Http::retry(2, 200)
                ->timeout(10)
                ->get("{$this->baseUrl}/search/movie", [
                    'api_key' => $this->apiKey,
                    'query' => $query,
                    'include_adult' => false,
                ]);

            if ($response->successful()) {
                return collect($response->json()['results'] ?? [])->take(5)->map(fn ($movie) => $this->normalizeMovie($movie));
            }

            Log::warning('TMDB search request failed', [
                'status' => $response->status(),
            ]);

            return collect([]);
        });
    }

    protected function normalizeMovie($movie): array
    {
        $data = $movie instanceof \ArrayAccess ? $movie : (array) $movie;

        return [
            'id' => $data['id'] ?? $data['tmdb_id'] ?? null,
            'title' => $data['title'] ?? $data['name'] ?? '',
            'poster_path' => $data['poster_path'] ?? null,
            'release_date' => $data['release_date'] ?? null,
            'tmdb_id' => $data['tmdb_id'] ?? $data['id'] ?? null,
        ];
    }
}
