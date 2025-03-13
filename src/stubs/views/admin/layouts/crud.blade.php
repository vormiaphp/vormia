<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Title -->
    <title>{{ $site_name }}</title>

    <!-- SEO -->
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <meta name="robots" content="no index, no follow">

    <!-- Main Header -->
    @include("$theme_dir.includes.head")
    <!-- End Main Header -->
    <link href="{{ asset("$theme_assets") }}/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset("$theme_assets") }}/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css"
        rel="stylesheet">

    <!-- Custom Css-->
    <link href='{{ asset("$theme_assets/custom/css/style.min.css") }}?v={{ time() }}' id="customcss-style"
        rel="stylesheet" type="text/css" />
</head>

<body data-layout="detached" data-topbar="colored">
    <div class="container-fluid">
        <!-- Begin page -->
        <div id="layout-wrapper">
            <header id="page-topbar">
                <div class="navbar-header">
                    <div class="container-fluid">
                        @include("$theme_dir.navs.page-topbar")
                    </div>
                </div>
            </header>

            <!-- ========== Left Sidebar Start ========== -->
            <div class="vertical-menu">
                <div class="h-100">
                    @include("$theme_dir.navs.vertical-menu")
                </div>
            </div>
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">
                <div class="page-content">
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            @include("$theme_dir.others.page-title")
                        </div>
                    </div>
                    <!-- end page title -->

                    <!-- Main Page -->
                    @yield('content')
                    <!-- End Main Page -->
                </div>
                <!-- End Page-content -->

                <!-- Footer -->
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-6">
                                <script>
                                    document.write(new Date().getFullYear());
                                </script>
                                &copy; ICEVI.
                            </div>
                            <div class="col-sm-6">
                                <div class="text-sm-right d-none d-sm-block">
                                    Developed by Vormia
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->
    </div>
    <!-- end container-fluid -->

    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    <!-- Main Footer -->
    @include("$theme_dir.includes.footer")
    <!-- End Main Footer -->

    <script src="{{ asset("$theme_assets") }}/libs/select2/js/select2.min.js"></script>
    <script src="{{ asset("$theme_assets") }}/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

    <script>
        !(function(i) {
            "use strict";

            function a() {}
            (a.prototype.init = function() {
                i(".select2").select2(),
                    i(".select2-limiting").select2({
                        maximumSelectionLength: 2
                    }),
                    i(".colorpicker-default").colorpicker({
                        format: "hex"
                    }),
                    i(".colorpicker-rgba").colorpicker(),
                    i("#colorpicker-horizontal").colorpicker({
                        color: "#88cc33",
                        horizontal: !0
                    }),
                    i("#colorpicker-inline").colorpicker({
                        color: "#DD0F20",
                        inline: !0,
                        container: !0
                    });
                var t = {};
            }),
            (i.AdvancedForm = new a()),
            (i.AdvancedForm.Constructor = a);
        })(window.jQuery),
        (function() {
            "use strict";
            window.jQuery.AdvancedForm.init();
        })();
    </script>
</body>

</html>
