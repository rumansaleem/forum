<?php

namespace App;

use App\Activity;
use App\Filters\ThreadsFilter;
use Illuminate\Database\Eloquent\Model;
use App\RecordsActivity;

class Thread extends Model
{
    use RecordsActivity;
    
    protected $guarded = [];

    protected $with = ['owner', 'channel'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('replyCount', function ($builder) {
            return $builder->withCount('replies');
        });

        static::deleting(function ($thread) {
            $thread->replies()->delete();
        });
    }
    
    public function owner()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    
    public function replies()
    {
        return $this->hasMany('App\Reply');
    }

    public function channel()
    {
        return $this->belongsTo('App\Channel');
    }

    public function scopeFilter($query, ThreadsFilter $filters)
    {
        return $filters->apply($query);
    }

    public function addReply($attributes)
    {
        $this->replies()->create($attributes);
    }
    
    public function path($append = '')
    {
        $path = "/threads/{$this->channel->slug}/{$this->id}";
        $path .= empty($append) ? '' : '/'.$append;
        return $path;
    }
}
