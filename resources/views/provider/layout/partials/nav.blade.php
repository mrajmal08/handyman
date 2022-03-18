<nav class="navbar navbar-inverse navbar-fixed-top" id="sidebar-wrapper" role="navigation">
    <ul class="nav sidebar-nav">
        <li>
            <a href="{{ route('provider.earnings') }}">@lang('main.update_earnings')</a>
        </li>
         <li>
            <a href="{{ route('provider.upcoming') }}">@lang('main.upcoming_services')</a>
        </li>
        <li>
            <a href="{{ route('provider.profile.index') }}">@lang('provider.profile.profile')</a>
        </li>

        <li>
            <a href="{{ url('/provider/logout') }}"
                onclick="event.preventDefault();
                         document.getElementById('logout-form').submit();">
                @lang('main.logout')
            </a>
        </li>
    </ul>
</nav>