<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ThreadWasUpdated;

class ThreadsTest extends TestCase
{
    use DatabaseMigrations;
    
    public function setUp()
    {
        parent::setUp();
        $this->thread = create('App\Thread');
    }

    /** @test */
    public function a_thread_has_a_owner()
    {
        $this->assertInstanceOf('App\User', $this->thread->owner);
    }

    /** @test */
    public function a_thread_has_many_replies()
    {
        $this->assertInstanceOf(Collection::class, $this->thread->replies);
    }

    /** @test */
    public function a_thread_belongs_to_a_channel()
    {
        $this->assertInstanceOf('App\Channel', $this->thread->channel);
    }

    /** @test */
    public function thread_can_create_path_string()
    {
        $this->assertEquals(
            "/threads/{$this->thread->channel->slug}/{$this->thread->slug}",
            $this->thread->path()
        );
    }

    /** @test */
    public function thread_can_add_a_reply()
    {
        $this->thread->addReply([
            'body' => 'FooBar',
            'user_id' => 1
        ]);
        $this->assertCount(1, $this->thread->replies);
    }

    /** @test */
    public function a_thread_can_be_subscribed_to_and_unsubscribed_from()
    {
        $this->signIn();
        $thread = create('App\Thread')->subscribe();

        $this->assertCount(1, $thread->subscriptions);

        $thread->unsubscribe();

        $this->assertCount(0, $thread->fresh()->subscriptions);
    }
    
    /** @test */
    public function it_notifies_registered_subscribers_when_it_has_a_new_reply()
    {
        Notification::fake();
        $this->signIn();

        $thread = create('App\Thread')->subscribe();

        $thread->addReply([
            'user_id' => 999,
            'body' => 'Foo Bar'
        ]);

        Notification::assertSentTo(auth()->user(), ThreadWasUpdated::class);
    }

    /** @test */
    public function it_can_track_if_thread_is_updated_for_authenticated_user_since_he_last_visited_it()
    {
        $this->signIn();
        $thread = create('App\Thread');

        $this->assertTrue($thread->hasChangedFor(auth()->id()));

        $thread->read();
        
        $this->assertFalse($thread->fresh()->hasChangedFor(auth()->id()));
    }

    /** @test */
    public function it_knows_if_user_is_subscribed_to()
    {
        $this->signIn();
        $thread = create('App\Thread');

        $this->assertFalse($thread->isSubscribedTo);

        $thread->subscribe();

        $this->assertTrue($thread->isSubscribedTo);
    }

    /** @test */
    public function it_increments_visits()
    {
        $thread = create('App\Thread');
        $this->assertSame(0, $thread->visits);

        $thread->increment('visits');

        $this->assertEquals(1, $thread->visits);
    }

    /** @test */
    public function it_can_mark_a_best_reply()
    {
        $thread = create('App\Thread');
        $reply = create('App\Reply', ['thread_id' => $thread->id]);

        $this->assertNull($thread->best_reply_id);
        $thread->markBestReply($reply);
        $this->assertEquals($thread->fresh()->best_reply_id, $reply->id);
    }

    /** @test */
    public function it_cleans_the_body_field_for_unwanted_html_tags()
    {
        $thread = create('App\Thread', [
            'body' => "<script>alert('foo')</script><p>Hello there <a href=\"#\" onclick=\"alert('gotcha');\">Click Me</a></p>"
        ]);
        $this->assertEquals($thread->body, "<p>Hello there <a href=\"#\">Click Me</a></p>");
    }
}
