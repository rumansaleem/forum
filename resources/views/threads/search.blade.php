@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <ais-index index-name="threads"
        query="{{ request('q') }}"
        app-id="{{ config('scout.algolia.id') }}"
        api-key="{{ config('scout.algolia.key') }}"
        >
            <div class="col-md-8">
                <ais-results>
                    <template slot-scope="{ result }">
                        <li>
                            <a :href="result.path">
                                <ais-highlight :result="result  " attribute-name="title"></ais-highlight>
                            </a>
                        </li>
                    </template>
                </ais-results>
            </div>
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Search
                    </div>
                    <div class="panel-body">
                        <ais-input placeholder="Find A thread" autofocus="true" class="form-control"></ais-input>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Filter Threads by
                    </div>
                    <div class="panel-body">
                        <ais-refinement-list attribute-name="channel.name"></ais-refinement-list>
                    </div>
                </div>
                @if( count($trending))
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Trending Threads
                        </div>
                        <div class="panel-body">
                            <ul class="list-group">
                                @foreach($trending as $thread)
                                    <li class="list-group-item"><a href="{{ $thread->link }}">{{$thread->title}}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </ais-index>
    </div>
</div>
@endsection
