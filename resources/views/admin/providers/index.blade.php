@extends('admin.layout.base')

@section('title', 'Providers ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
               
        @if(Setting::get('demo_mode', 0) != "0") 
                <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : No Permission to Edit and Delete.
                </div>
         @endif
            <h5 class="mb-1">@lang('main.provider')s</h5>
            <a href="{{ route('admin.provider.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add New @lang('main.provider')</a>
            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Accepted Requests</th>
                        <th>Cancelled Requests</th>
                        <th>Documents / Service Type</th>
                        <th>Online</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($providers as $index => $provider)
                    <tr>
                        <td>{{$index + 1}}</td>
                        <td>{{$provider->first_name}} {{$provider->last_name}}</td>
                        @if(Setting::get('demo_mode', 0) == 1)
                        <td>{{ substr($provider->email, 0, 3).'****'.substr($provider->email, strpos($provider->email, "@")) }}</td>
                        @else
                        <td>{{$provider->email}}</td>
                        @endif
                        @if(Setting::get('demo_mode', 0) == 1)
                        <td>{{ substr($provider->mobile, 0, 5).'****' }}</td>
                        @else
                        <td>{{ $provider->mobile}}</td>
                        @endif
                        <td>{{$provider->accepted_requests}}</td>
                        <td>{{$provider->total_requests - $provider->accepted_requests }}</td>
                        <td>
                            @if($provider->pending_documents() > 0 || $provider->service == null)
                                <a class="btn btn-danger label-right" href="{{route('admin.provider.document.index', $provider->id )}}">Attention! <span class="btn-label">{{ $provider->pending_documents() }}</span></a>
                            @else
                                <a class="btn btn-success" href="{{route('admin.provider.document.index', $provider->id )}}">All Set!</a>
                            @endif
                        </td>
                        <td>
                            @if($provider->service)
                                @if($provider->service->status == 'active')
                                    <label class="btn btn-primary">Yes</label>
                                @else
                                    <label class="btn btn-warning">No</label>
                                @endif
                            @else
                                <label class="btn btn-danger">N/A</label>
                            @endif
                        </td>
                        <td>
                            <div class="input-group-btn">
                                @if($provider->status == 'approved')
                                    <a class="btn btn-danger" href="{{route('admin.provider.disapprove', $provider->id )}}">Disable</a>
                                @else
                                    <a class="btn btn-success" href="{{route('admin.provider.approve', $provider->id )}}">Enable</a>
                                @endif
                                <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">Action
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="{{ route('admin.provider.request', $provider->id) }}" class="btn btn-default"><i class="fa fa-search"></i> History</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.provider.statement', $provider->id) }}" class="btn btn-default"><i class="fa fa-document"></i> Statements</a>
                                    </li>
                                    <li>
                                    @if(Setting::get('demo_mode', 0) == "0")
                                        <a href="{{ route('admin.provider.edit', $provider->id) }}" class="btn btn-default"><i class="fa fa-pencil"></i> Edit</a>
                                        @endif
                                    </li>
                                    <li>
                                        <form action="{{ route('admin.provider.destroy', $provider->id) }}" method="POST">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="_method" value="DELETE">
                                            @if(Setting::get('demo_mode', 0) == "0")
                                            <button class="btn btn-default look-a-like" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i> Delete</button>
                                            @endif
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Accepted Requests</th>
                        <th>Cancelled Requests</th>
                        <th>Documents / Service Type</th>
                        <th>Online</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection