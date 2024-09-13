<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('assets/errors/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/errors/css/bootstrap.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/errors/css/demo.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/errors/css/templatemo-style.css') }}">

    <script type="text/javascript" src="{{ asset('assets/errors/js/modernizr.custom.86080.js') }}"></script>

</head>

<body>

<div id="particles-js"></div>

<ul class="cb-slideshow">
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
</ul>

<div class="container-fluid">
    <div class="row cb-slideshow-text-container ">
        <div class="tm-content col-xl-6 col-sm-8 col-xs-8 section mx-auto">
            <section class="d-flex flex-column align-items-center justify-content-center" style="line-height: 90px;">
                @yield('content')
                <div class="tm-social-icons-container text-xs-center">
                    <a href="#" class="tm-social-link"><i class="fa fa-facebook"></i></a>
                    <a href="#" class="tm-social-link"><i class="fa fa-google-plus"></i></a>
                    <a href="#" class="tm-social-link"><i class="fa fa-twitter"></i></a>
                    <a href="#" class="tm-social-link"><i class="fa fa-linkedin"></i></a>
                </div>
            </section>
        </div>
    </div>
    <div class="footer-link">
        @yield('footer')
    </div>
</div>
</body>

<script type="text/javascript" src="{{ asset('assets/errors/js/particles.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/errors/js/app.js') }}"></script>
</html>
