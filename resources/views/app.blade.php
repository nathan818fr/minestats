<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- TODO(nathan818): Add description meta - <meta name="description" content=""> --}}
    <meta name="author" content="Nathan Poirier">
    <link rel="icon" href="favicon.ico">

    <title>@yield('title') - MineStats</title>

    <!-- CSS -->
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>

<header class="clearfix">
    <div class="container">
        <div id="logo">
            <a href="{{ route('serversList') }}">
                <h1>MineStats</h1>
            </a>
        </div>
    </div>
</header>

@yield('content')

<!-- JS -->
<script>
    window.Laravel = {!! json_encode([
                        'csrfToken' => csrf_token(),
                    ]) !!};
</script>
<script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
