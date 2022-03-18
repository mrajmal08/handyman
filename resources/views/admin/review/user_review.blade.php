@extends('admin.layout.base')

@section('title', 'Reviews ')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            
            <div class="box box-block bg-white">
                <h5 class="mb-1">@lang('main.user') Reviews</h5>
                <table class="table table-striped table-bordered dataTable" id="table-2">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Request ID</th>
                            <th>@lang('main.user') Name</th>
                            <th>@lang('main.provider') Name</th>
                            <th>Rating</th>
                            <th>Date & Time</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($Reviews as $index => $review)
                        <tr>
                            <td>{{$index + 1}}</td>
                            <td>{{$review->request_id}}</td>
                            @if($review->user)
                                <td>{{$review->user?$review->user->first_name:''}} {{$review->user?$review->user->last_name:''}}</td>
                            @else
                                <td>-</td>
                            @endif
                            @if($review->provider)
                                <td>{{$review->provider?$review->provider->first_name:''}} {{$review->provider?$review->provider->last_name:''}}</td>
                            @else
                                <td>-</td>
                            @endif

                            <td>
                                <div className="rating-outer">
                                    <input type="hidden" value="{{$review->user_rating}}" name="rating" class="rating"/>
                                </div>
                            </td>
                            <td>{{$review->created_at}}</td>
                            <td>{{$review->user_comment}}</td>
                            
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Request ID</th>
                            <th>@lang('main.user') Name</th>
                            <th>@lang('main.provider') Name</th>
                            <th>Rating</th>
                            <th>Date & Time</th>
                            <th>Comments</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
        </div>
    </div>
@endsection