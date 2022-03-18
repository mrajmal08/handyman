@extends('provider.layout.auth')

@section('content')

<h3>Reset Password</h3>

@if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
@endif
<form role="form" method="POST" action="{{ url('/provider/password/reset') }}">
    {{ csrf_field() }}
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="col-md-12">
        <input type="email" class="form-control" name="email" placeholder="Email Address" value="{{ old('email') }}">

        @if ($errors->has('email'))
            <span class="help-block">
                <strong>{{ $errors->first('email') }}</strong>
            </span>
        @endif                        
    </div>
    <div class="col-md-12">
        <input type="password" class="form-control" name="password" placeholder="Password">

        @if ($errors->has('password'))
            <span class="help-block">
                <strong>{{ $errors->first('password') }}</strong>
            </span>
        @endif
    </div>
    <div class="col-md-12">
        <input type="password" placeholder="Re-type Password" class="form-control" name="password_confirmation">

        @if ($errors->has('password_confirmation'))
            <span class="help-block">
                <strong>{{ $errors->first('password_confirmation') }}</strong>
            </span>
        @endif
    </div>
    
    <div class="col-md-12">
        <button class="log-teal-btn" type="submit">RESET PASSWORD</button>
    </div>
</form>     

@endsection
