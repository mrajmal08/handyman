@extends('admin.layout.base')

@section('title', 'Users ')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                @if(Setting::get('demo_mode', 0) != "0")
                <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : No Permission to Edit and Delete.
               </div>
                @endif
                
                <h5 class="mb-1">@lang('main.user')s</h5>
                <a href="{{ route('admin.user.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add New @lang('main.user')</a>
                <table class="table table-striped table-bordered dataTable" id="table-2">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Rating</th>
                            <th>Wallet Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $index => $user)
                        <tr>
                            <td>{{$index + 1}}</td>
                            <td>{{$user->first_name}}</td>
                            <td>{{$user->last_name}}</td>

                             @if(Setting::get('demo_mode', 0) == 1)
                            <td>{{ substr($user->email, 0, 3).'****'.substr($user->email, strpos($user->email, "@")) }}</td>
                            @else
                            <td>{{ $user->email }}</td>
                            @endif
                            @if(Setting::get('demo_mode', 0) == 1)
                            <td>{{ substr($user->mobile, 0, 5).'****' }}</td>
                            @else
                            <td>{{ $user->mobile }}</td>
                            @endif
                            <td>{{$user->rating}}</td>
                            <td> {{ currency($user->wallet_balance)}}</td>
                            <td>
                                <form action="{{ route('admin.user.destroy', $user->id) }}" method="POST">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="_method" value="DELETE">
                                    
                                    <a href="{{ route('admin.user.request', $user->id) }}" class="btn btn-info"><i class="fa fa-search"></i> History</a>
                                    @if(Setting::get('demo_mode', 0) == "0")
                                    <a href="{{ route('admin.user.edit', $user->id) }}" class="btn btn-info"><i class="fa fa-pencil"></i> Edit</a>
                                    <button class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i> Delete</button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Rating</th>
                            <th>Wallet Amount</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
        </div>
    </div>
@endsection