<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Illuminate\Notifications\Notification;
use App\Notifications\ThreadWasUpdated;

class ThreadSubscription extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }
    public function notify($reply)
    {
        return $this->user->notify(new ThreadWasUpdated($this->thread, $reply));
    }
}
