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

		<!-- DataTables -->
		<link href="{{ asset("$theme_assets") }}/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet"
			type="text/css" />
		<link href="{{ asset("$theme_assets") }}/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css"
			rel="stylesheet" type="text/css" />

		<!-- Responsive datatable examples -->
		<link href="{{ asset("$theme_assets") }}/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css"
			rel="stylesheet" type="text/css" />

		<!-- Main Header -->
		@include("$theme_dir.includes.head")
		<!-- End Main Header -->

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

		<!-- Main Footer -->
		@include("$theme_dir.includes.footer")
		<!-- End Main Footer -->

		<!-- Required datatable js -->
		<script src='{{ asset("$theme_assets") }}/libs/datatables.net/js/jquery.dataTables.min.js'></script>
		<script src="{{ asset("$theme_assets") }}/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
		<!-- Buttons examples -->
		<script src="{{ asset("$theme_assets") }}/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
		<script src="{{ asset("$theme_assets") }}/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
		<script src="{{ asset("$theme_assets") }}/libs/jszip/jszip.min.js"></script>
		<script src="{{ asset("$theme_assets") }}/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
		<script src="{{ asset("$theme_assets") }}/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
		<script src="{{ asset("$theme_assets") }}/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>
		<!-- Responsive examples -->
		<script src="{{ asset("$theme_assets") }}/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
		<script src="{{ asset("$theme_assets") }}/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

		<!-- Datatable init js -->
		<script src="{{ asset("$theme_assets") }}/js/pages/datatables.init.js"></script>

	</body>

</html>
