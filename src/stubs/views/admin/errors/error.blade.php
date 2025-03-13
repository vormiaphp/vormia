<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>{{ $site_name }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <meta name="robots" content="no index, no follow">

    <!-- App favicon -->
    <link rel="shortcut icon" href='{{ asset("$theme_assets/custom/img/favicon.ico") }}' type="image/x-icon">

    <!-- Bootstrap Css -->
    <link href='{{ asset("$theme_assets/css/bootstrap.min.css") }}' id="bootstrap-style" rel="stylesheet"
        type="text/css" />
    <!-- Icons Css -->
    <link href='{{ asset("$theme_assets/css/icons.min.css") }}' rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href='{{ asset("$theme_assets/css/app.min.css") }}' id="app-style" rel="stylesheet" type="text/css" />
    <!-- Custom Css-->
    <link href='{{ asset("$theme_assets/custom/css/style.min.css") }}?v={{ time() }}' id="customcss-style"
        rel="stylesheet" type="text/css" />

</head>

<body style="background-color: #aac3dc !important;">
    <div class="home-btn d-none d-sm-block">
        <a href="{!! url('') !!}" class="text-dark"><i class="fas fa-home h2 sks-color-red"></i></a>
    </div>
    <div class="account-pages my-5 pt-sm-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card overflow-hidden">
                        <div class="card-body">

                            <div class="text-center p-3">
                                <div class="img">
                                    <img src="{{ asset("$theme_assets/images/error-img.png") }}" class="img-fluid"
                                        alt="">
                                </div>

                                <!-- Main Page -->
                                @yield('content')
                                <!-- End Main Page -->

                                <a class="btn btn-primary mb-4 waves-effect waves-light"
                                    href="{!! url('vrm-dashboard') !!}"><i class="mdi mdi-home"></i> Back to Dashboard</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script src='{{ asset("$theme_assets/libs/jquery/jquery.min.js") }}'></script>
    <script src='{{ asset("$theme_assets/libs/bootstrap/js/bootstrap.bundle.min.js") }}'></script>
    <script src='{{ asset("$theme_assets/libs/metismenu/metisMenu.min.js") }}'></script>
    <script src='{{ asset("$theme_assets/libs/simplebar/simplebar.min.js") }}'></script>
    <script src='{{ asset("$theme_assets/libs/node-waves/waves.min.js") }}'></script>
    <script src='{{ asset("$theme_assets/js/app.js") }}'></script>

</body>

</html>
