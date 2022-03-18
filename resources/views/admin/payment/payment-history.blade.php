@extends('admin.layout.base')

@section('title', 'Payment History ')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            <div class="box box-block bg-white">
                <h5 class="mb-1">Payment History</h5>
                <table class="table table-striped table-bordered dataTable" id="table-2">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Transaction ID</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Total Amount</th>
                            <th>Payment Mode</th>
                            <th>Payment Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($payments as $index => $payment)
                        <tr>
                            <td>{{$payment->booking_id}}</td>
                            @if($payment->payment)
                                <td>{{$payment->payment->payment_id ? $payment->payment->payment_id : 'NA'}}</td>
                            @else
                                <td>-</td>
                            @endif
                            @if($payment->user)
                                <td>{{$payment->user->first_name}} {{$payment->user->last_name}}</td>
                            @else
                                <td>-</td>
                            @endif
                            @if($payment->provider)
                                <td>{{$payment->provider->first_name}} {{$payment->provider->last_name}}</td>
                            @else
                                <td>-</td>
                            @endif
                            @if($payment->payment)
                                <td>{{currency($payment->payment->total)}}</td>
                            @else
                                <td>-</td>
                            @endif
                            <td>{{$payment->payment_mode}}</td>
                            <td>
                                @if($payment->paid)
                                    Paid
                                @else
                                    Not Paid
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Request ID</th>
                            <th>Transaction ID</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Total Amount</th>
                            <th>Payment Mode</th>
                            <th>Payment Status</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
        </div>
    </div>
@endsection