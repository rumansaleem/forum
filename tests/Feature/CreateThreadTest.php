<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Auth\AuthenticationException;
use App\Rules\Recaptcha;

class CreateThreadTest extends TestCase
{
    use DatabaseMigrations;
    
    public function setUp()
    {
        parent::setUp();
    
        app()->singleton(Recaptcha::class, function () {
            $mock =  \Mockery::mock(Recaptcha::class);
            $mock->shouldReceive('passes')->andReturn(true);
            return $mock;
        });
    }
    
    /** @test */
    public function a_guest_user_may_not_create_a_thread()
    {
        $this->withExceptionHandling();

        $this->get('/threads/create')
            ->assertRedirect('/login');

        $this->post('/threads')
            ->assertRedirect('/login');
    }
    
    /** @test */
    public function user_must_confrm_their_emails_before_start_creating_threads()
    {
        $user = factory('App\User')->states('unconfirmed')->create();
        
        $this->withExceptionHandling()->signIn($user);
        $thread = make('App\Thread');
        $this->post('/threads', $thread->toArray())
            ->assertRedirect('/threads')
            ->assertSessionHas('flash', 'You must confirm email, before posting anything!');
    }
    
    /** @test */
    public function a_logged_in_user_can_create_a_thread()
    {
        $response = $this->publishThread([
            'body' => 'Some Body',
            'title' => 'Some Title'
        ]);

        $this->get($response->headers->get('Location'))
            ->assertSee('Some Body')
            ->assertSee('Some Title');
    }

    /** @test */
    public function a_thread_requires_title()
    {
        $this->withExceptionHandling()
            ->publishThread(['title' => null])
            ->assertSessionHasErrors('title');
    }

    /** @test */
    public function a_thread_requires_body()
    {
        $this->withExceptionHandling()
            ->publishThread(['body' => null])
            ->assertSessionHasErrors('body');
    }

    /** @test */
    public function a_thread_requires_recaptcha()
    {
        $response = $this->publishThread([
            'g-recaptcha-response' => null
        ])->assertSessionHasErrors('g-recaptcha-response');
    }

    /** @test */
    public function a_thread_validates_recaptcha()
    {
        unset(app()[Recaptcha::class]);
        
        $this->publishThread([
            'g-recaptcha-response' => 'invalid-recaptcha',
        ])->assertSessionHasErrors('g-recaptcha-response');
    }
    
    /** @test */
    public function a_thread_requires_a_valid_channel_id()
    {
        create('App\Channel', [], 2);
        
        $this->withExceptionHandling()
        ->publishThread(['channel_id' => null])
        ->assertSessionHasErrors('channel_id');
        
        $this->publishThread(['channel_id' => 999])
        ->assertSessionHasErrors('channel_id');
    }

    /** @test */
    public function a_thread_requires_a_unique_slug()
    {
        $this->signIn();
        
        create('App\Thread', [], 2);
        
        $thread = create('App\Thread', ['title' => 'See My Post']);

        $this->post('/threads', $thread->toArray() + ['g-recaptcha-response' => 'valid-token']);

        $this->assertDatabaseHas('threads', ['title' => 'See My Post', 'slug' => 'see-my-post-4']);

        $this->post('/threads', $thread->toArray() + ['g-recaptcha-response' => 'valid-token']);

        $this->assertDatabaseHas('threads', ['title' => 'See My Post', 'slug' => 'see-my-post-5']);
    }

    /** @test */
    public function a_thread_that_has_title_ending_with_number_shoud_genereate_proper_slug()
    {
        $this->signIn();
        
        $thread = create('App\Thread', ['title' => 'Number Title 24']);

        $this->post('/threads', $thread->toArray() + ['g-recaptcha-response' => 'valid-token']);

        $this->assertDatabaseHas('threads', ['title' => 'Number Title 24', 'slug' => 'number-title-24-2']);

        $this->post('/threads', $thread->toArray() + ['g-recaptcha-response' => 'valid-token']);

        $this->assertDatabaseHas('threads', ['title' => 'Number Title 24', 'slug' => 'number-title-24-3']);
    }
    
    /////////////////////////////////////////////////////////
    //                  THREAD DELETE TESTS                //
    /////////////////////////////////////////////////////////
    

    /** @test */
    public function an_unauthorized_user_cannot_delete_thread()
    {
        $this->withExceptionHandling();
        $thread = create('App\Thread');
        
        $this->delete($thread->path())
            ->assertRedirect('/login');

        $this->signIn();

        $this->delete($thread->path())
            ->assertStatus(403);
        $this->assertDatabaseHas('threads', ['id' => $thread->id]);
    }

    /** @test */
    public function an_authorized_user_can_delete_thread()
    {
        $this->signIn();

        $thread = create('App\Thread', ['user_id' => auth()->id()]);
        $reply = create('App\Reply', ['thread_id' => $thread->id]);

        $this->delete($thread->path())
            ->assertRedirect('/threads');
        $this->assertDatabaseMissing('threads', ['id' => $thread->id]);
        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);
        $this->assertDatabaseMissing('activities', [
            'subject_id' => $thread->id,
            'subject_type' => get_class($thread)
        ]);

        $this->assertDatabaseMissing('activities', [
            'subject_id' => $reply->id,
            'subject_type' => get_class($reply)
        ]);
    }

    // Local Helpers

    protected function publishThread($overrides = [])
    {
        $this->withExceptionHandling()->signIn();
        $thread = make('App\Thread', $overrides);
        return $this->post('/threads', $thread->toArray() + ['g-recaptcha-response' => 'valid-token']);
    }
}
