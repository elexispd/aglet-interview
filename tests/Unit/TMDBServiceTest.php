<?php

namespace Tests\Unit;

use App\Services\TMDBService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TMDBServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config()->set('services.tmdb.key', 'test-key');
    }

    public function test_it_paginates_45_movies_into_5_pages_of_9(): void
    {
        $page1 = collect(range(1, 20))->map(fn ($id) => [
            'id' => $id,
            'title' => "Movie {$id}",
            'poster_path' => "/p{$id}.jpg",
            'release_date' => '2020-01-01',
        ])->all();

        $page2 = collect(range(21, 40))->map(fn ($id) => [
            'id' => $id,
            'title' => "Movie {$id}",
            'poster_path' => "/p{$id}.jpg",
            'release_date' => '2020-01-01',
        ])->all();

        $page3 = collect(range(41, 60))->map(fn ($id) => [
            'id' => $id,
            'title' => "Movie {$id}",
            'poster_path' => "/p{$id}.jpg",
            'release_date' => '2020-01-01',
        ])->all();

        Http::fake([
            'api.themoviedb.org/3/movie/popular*page=1*' => Http::response(['results' => $page1], 200),
            'api.themoviedb.org/3/movie/popular*page=2*' => Http::response(['results' => $page2], 200),
            'api.themoviedb.org/3/movie/popular*page=3*' => Http::response(['results' => $page3], 200),
        ]);

        $service = app(TMDBService::class);

        $moviesPage1 = $service->getMovies(1);
        $this->assertCount(9, $moviesPage1);
        $this->assertSame(1, $moviesPage1->first()['id']);
        $this->assertSame(9, $moviesPage1->last()['id']);

        $moviesPage5 = $service->getMovies(5);
        $this->assertCount(9, $moviesPage5);
        $this->assertSame(37, $moviesPage5->first()['id']);
        $this->assertSame(45, $moviesPage5->last()['id']);
    }

    public function test_it_returns_error_when_api_key_missing(): void
    {
        config()->set('services.tmdb.key', '');

        $result = app(TMDBService::class)->getMoviesPage(1);

        $this->assertSame('TMDB_API_KEY is missing.', $result['error']);
        $this->assertTrue($result['movies']->isEmpty());
    }
}
