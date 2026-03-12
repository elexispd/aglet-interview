<?php

namespace Tests\Unit;

use App\Models\Favorite;
use Tests\TestCase;

class FavoriteUuidTest extends TestCase
{
    public function test_favorites_are_configured_for_uuid_primary_key(): void
    {
        $favorite = new Favorite;

        $this->assertFalse($favorite->incrementing);
        $this->assertSame('string', $favorite->getKeyType());

        $uuid = $favorite->newUniqueId();
        $this->assertIsString($uuid);
        $this->assertSame(36, strlen($uuid));
    }
}
