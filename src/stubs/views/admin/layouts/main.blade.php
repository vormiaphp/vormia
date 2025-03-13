<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Global Header -->
    @include("$theme_dir.includes.global._head")
    <!-- End Global Header -->

    <!-- Main Header -->
    @include("$theme_dir.includes.head")
    <!-- End Main Header -->
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
                                &copy;.
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

    <!-- Global Footer -->
    @include("$theme_dir.includes.global._footer")
    <!-- End Global Footer -->

    <!-- Main Footer -->
    @include("$theme_dir.includes.footer")
    <!-- End Main Footer -->

</body>

</html>
