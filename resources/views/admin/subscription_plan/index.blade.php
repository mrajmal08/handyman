@extends('admin.layout.base')

@section('title', 'Subscription Plans')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
           @if(Setting::get('demo_mode') == 1)
        <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : No Permission to Edit and Delete.
                </div>
                @endif 
            <h5 class="mb-1">Subscription Plans</h5>
            <a href="{{ url('admin/subscription_plans/create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add New Subscription Plan</a>
            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Plan Name</th>
                        <th>Amount</th>
                        <th>Validity</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($subscription_plans as $index => $subscription_plan)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $subscription_plan->name }}</td>
                        <td>{{ $subscription_plan->amount }}</td>
                        <td>{{ $subscription_plan->validity }}</td>
                        <td>{{ $subscription_plan->description }}</td>
                        <td>
                            <form action="{{ url('admin/subscription_plans/destroy/'.$subscription_plan->id) }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                @if( Setting::get('demo_mode') == 0)
                                <a href="{{ url('admin/subscription_plans/edit/'.$subscription_plan->id) }}" class="btn btn-info btn-block">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                                <button class="btn btn-danger btn-block" onclick="return confirm('Are you sure?')">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                         <th>ID</th>
                        <th>Plan Name</th>
                        <th>Amount</th>
                        <th>Validity</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>
      
    </div>
</div>


@endsection