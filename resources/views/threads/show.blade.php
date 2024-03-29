@extends('layouts.app')
@section('header')
    <link rel="stylesheet" href="/css/vendor/jquery.atwho.css">
@endsection
@section('content')
<thread-view :thread="{{ $thread }}" inline-template> 
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                @include('threads._content')
                <replies :locked="locked" @removed="repliesCount--" @added="repliesCount++"></replies>
            </div>
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <p>
                            This thread was posted {{ $thread->created_at->diffForHumans() }} by 
                            <a href="/profiles/{{ $thread->owner->name }}">{{ $thread->owner->name }}</a> and has currently has 
                            <span v-text="repliesCount"></span> {{ str_plural('reply', $thread->replies_count) }}
                        </p>
                        <subscribe-button :active="{{ json_encode($thread->isSubscribedTo) }}" v-if="signedIn"></subscribe-button>
                        <button class="btn btn-danger" @click="toggleLock" v-if="authorize('isAdmin')" v-text="locked ? 'Unlock' : 'Lock'"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</thread-view>
@endsection
