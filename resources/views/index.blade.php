@extends('user.layout.app')

@section('content')

    <?php $banner_bg = asset('asset/img/banner-bg.jpg'); ?>
        <div class="banner row no-margin" style="background-image: url('{{$banner_bg}}');">
            <div class="banner-overlay"></div>
            <div class="container">
                <div class="col-md-8">
                    <h2 class="banner-head"><span class="strong">Get there</span><br>Your day belongs to you</h2>
                </div>
                <div class="col-md-4">
                    <div class="banner-form">
                        <div class="row no-margin fields">
                            <div class="left">
                                <img src="{{asset('asset/img/ride-form-icon.png')}}">
                            </div>
                            <div class="right">
                                <a href="{{url('register')}}">
                                    <h3>Get a @lang('main.service')</h3>
                                    <h5>SIGN UP <i class="fa fa-chevron-right"></i></h5>
                                </a>
                            </div>
                        </div>
                        <div class="row no-margin fields">
                            <div class="left">
                                <img src="{{asset('asset/img/ride-form-icon.png')}}">
                            </div>
                            <div class="right">
                                <a href="{{url('/provider/register')}}">
                                    <h3>Provide a @lang('main.service')</h3>
                                    <h5>SIGN UP <i class="fa fa-chevron-right"></i></h5>
                                </a>
                            </div>
                        </div>

                        <p class="note-or">Or <a href="{{url('/provider/login')}}">sign in</a> with your @lang('main.provider') account.</p>
                        
                    </div>
                </div>
            </div>
        </div>

@endsection
