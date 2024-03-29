<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Favorite;
use App\RecordsActivity;
use Carbon\Carbon;

class Reply extends Model
{
    use Favoritable, RecordsActivity;
    
    protected $fillable = ['body', 'thread_id', 'user_id'];
    protected $with = ['owner', 'favorites'];
    protected $appends = ['favoritesCount', 'isFavorited', 'isBest'];
    
    protected static function boot()
    {
        parent::boot();

        static::created(function ($reply) {
            $reply->thread->increment('replies_count');
        });

        static::deleted(function ($reply) {
            $reply->thread->decrement('replies_count');
        });
    }

    public function owner()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function thread()
    {
        return $this->belongsTo('App\Thread');
    }

    public function mentionedUsers()
    {
        preg_match_all('/\\@([\\w\\-]+)/', $this->body, $matches);
        return $matches[1];
    }

    public function wasJustPublished()
    {
        return $this->created_at > Carbon::now()->subMinute();
    }
    public function isBest()
    {
        return $this->thread->best_reply_id == $this->id;
    }
    
    public function path()
    {
        return $this->thread->path() . "#reply-{$this->id}";
    }

    public function setBodyAttribute($body)
    {
        $this->attributes['body'] = preg_replace('/@([\\w\\-]+)/', '<a href="/profiles/$1">$0</a>', $body);
    }

    public function getIsBestAttribute()
    {
        return $this->isBest();
    }

    public function getBodyAttribute($body)
    {
        return \Purify::clean($body);
    }
}
