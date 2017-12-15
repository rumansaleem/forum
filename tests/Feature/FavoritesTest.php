<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class FavoritesTest extends TestCase
{
    use DatabaseMigrations;
    /** @test */
    public function an_unauthenticated_user_can_not_favorite_a_reply()
    {
        $this->withExceptionHandling()
            ->post('replies/1/favorite')
            ->assertRedirectedToRoute('login');
    }

    /** @test */
    public function an_authenticated_user_can_favorite_a_reply()
    {
        $this->signIn();

        $reply = create('App\Reply');

        $this->post('replies/'. $reply->id .'/favorite')
            ->assertCount(1, $reply->favorites);
    }

    /** @test */
    public function an_authenticated_user_can_favorite_a_reply_only_once()
    {
        $this->signIn();

        $reply = create('App\Reply');

        $this->post('replies/'. $reply->id .'/favorite');
        $this->post('replies/'. $reply->id .'/favorite')
            ->assertCount(1, $reply->favorites);
    }
}
