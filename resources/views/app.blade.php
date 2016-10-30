<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- TODO(nathan818): Add description meta - <meta name="description" content=""> --}}
    <meta name="author" content="Nathan Poirier">
    <link rel="icon" href="{{ rev_asset('assets/img/favicon.png')  }}">

    <title>@yield('title') - MineStats</title>

    <!-- CSS -->
    <link href="{{ rev_asset('assets/css/app.css') }}" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div id="site">

    <nav id="header" class="navbar navbar-inverse navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                        aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{{ route('serversList') }}">MineStats</a>
            </div>
            <div id="navbar" class="collapse navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a href="{{ route('serversList') }}">@lang('server.servers_list')</a>
                    </li>
                    @if (auth()->check())
                        @can('view', \MineStats\Models\User::class)
                            <li>
                                <a href="{{ route('usersList') }}">@lang('user.users_list')</a>
                            </li>
                        @endcan
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                               aria-haspopup="true"
                               aria-expanded="false">
                                <i class="fa fa-user"></i> {{ auth()->user()->username }}
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="{{ route('account') }}">
                                        @lang('user.my_account')</a>
                                </li>
                                <li>
                                    <a href="{{ \MineStats\Http\Controllers\Web\AuthController::getLogoutUrl() }}">
                                        @lang('auth.logout')</a>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('login') }}">@lang('auth.login')</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    @yield('content')
</div>

<footer>
    @lang('general.footer.made_by', ['authors' => '<a href="https://github.com/nathan818fr">Nathan Poirier (nathan818)</a>'])
    -
    @lang('general.footer.source_code_available', ['source' => '<a href="https://github.com/nathan818fr/minestats">Github</a>'])
</footer>

<!-- JS -->
<script>
    window.Laravel = {!! json_encode([
                        'csrfToken' => csrf_token(),
                        'locale' => App::getLocale(),
                    ]) !!};
</script>
<script src="{{ rev_asset('assets/js/app.js') }}"></script>
</body>
</html>
