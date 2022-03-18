<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{Setting::get('site_title','Tranxit')}}</title>

    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" type="image/png" href="{{ Setting::get('site_icon') }}"/>

    <link href="{{asset('asset/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('asset/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">
    <link href="{{asset('asset/css/style.css')}}" rel="stylesheet">
</head>

<body>
@include('user.notification')
    <div id="wrapper">
        <div class="overlay" id="overlayer" data-toggle="offcanvas"></div>


             <!-- Sidebar -->
            <nav class="navbar navbar-inverse navbar-fixed-top" id="sidebar-wrapper" role="navigation">
                <ul class="nav sidebar-nav">
                    <li>                   
                    </li>
                    <li class="full-white">
                        <a href="{{ url('/register') }}">GET A SERVICE</a>
                    </li>
                    <li class="white-border">
                        <a href="{{ url('/provider/login') }}">BECOME A @lang('main.provider')</a>
                    </li>

                    <li>
                        <a href="{{url('/privacy')}}">Privacy Policy</a>
                    </li>

                </ul>
            </nav>
            <!-- /#sidebar-wrapper -->

            <div id="page-content-wrapper">

            <header>
                <nav class="navbar navbar-fixed-top">
                  <div class="container-fluid">
                    <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                      </button>

                      <button type="button" class="hamburger is-closed" data-toggle="offcanvas">
                        <span class="hamb-top"></span>
                        <span class="hamb-middle"></span>
                        <span class="hamb-bottom"></span>
                    </button>

                      <a class="navbar-brand" href="{{url('/')}}"><img src="{{ Setting::get('site_logo',asset('asset/img/logo.png'))}}"></a>
                    </div>
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    
                    <ul class="nav navbar-nav navbar-right">
                      <li><a href="{{url('/login')}}">Signin</a></li>
                      <li><a class="menu-btn" href="{{url('/provider/login')}}">Become a @lang('main.provider')</a></li>
                    </ul>
                    </div>
                  </div>
                </nav>
            </header>

            @yield('content')
                <div class="page-content no-margin">

                    <div class="footer home-footer row no-margin no-padding">
                        
                        <div class="store-strip white-bg row no-margin">
                            <div class="container">
                                <div class="col-md-6">
                                    <h5>Download the app to enjoy the services</h5>
                                </div>

                                <div class="col-md-6 text-right">
                                    <a href="{{Setting::get('app_store_link','#')}}">
                                        <img src="{{asset('asset/img/appstore.png')}}">
                                    </a>
                                    <a href="{{Setting::get('play_store_link','#')}}">
                                        <img src="{{asset('asset/img/playstore.png')}}">
                                    </a>
                                </div>

                            </div>
                        </div>

                        <div class="store-strip gray-bg row no-margin">
                            <div class="container">
                                <div class="col-md-6 ">
                                    <a href="{{Setting::get('app_store_link','#')}}">
                                        <img src="{{asset('asset/img/appstore.png')}}">
                                    </a>
                                    <a href="{{Setting::get('play_store_link','#')}}">
                                        <img src="{{asset('asset/img/playstore.png')}}">
                                    </a>
                                </div>

                                <div class="col-md-6 text-right">
                                    <h5>Download the app to become a service @lang('main.provider')</h5>
                                </div>

                                

                            </div>
                        </div>
                            
                        <div class="row no-margin">
                                <div class="col-md-12 copy no-margin">
                                    <p>Copyrights {{date('Y')}} {{Setting::get('site_title','Tranxit')}}.</p>
                                </div>
                        </div>

                    </div>
                </div>
            </div>


    </div>

    <script src="{{asset('asset/js/jquery.min.js')}}"></script>
    <script src="{{asset('asset/js/bootstrap.min.js')}}"></script>
    <script src="{{asset('asset/js/scripts.js')}}"></script>
    @if(Setting::get('demo_mode', 0) == 1)
        <!-- Start of LiveChat (www.livechatinc.com) code -->
        <script type="text/javascript">
            window.__lc = window.__lc || {};
            window.__lc.license = 8256261;
            (function() {
                var lc = document.createElement('script'); lc.type = 'text/javascript'; lc.async = true;
                lc.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cdn.livechatinc.com/tracking.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(lc, s);
            })();
        </script>
        
        <!-- End of LiveChat code -->
    @endif
    <script>
$(document).ready(function()
{
$('span.cross-icon').click(function()
{
$('.header-top').slideUp();
$('.navbar').css('top','0px');
});

});
$(window).scroll(function()
{
if($(this).scrollTop()>50)
{
$('header>nav.navbar.navbar-fixed-top').addClass('fixedmenu')
}
else{
$('header>nav.navbar.navbar-fixed-top').removeClass('fixedmenu');
}
});
</script>
</body>
</html>
