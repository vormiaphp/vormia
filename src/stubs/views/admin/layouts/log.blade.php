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

		<!-- Favicon Load -->
		<link rel="apple-touch-icon" sizes="180x180"
			href="{{ asset("$theme_assets") }}/custom/img/logo/favicon/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32"
			href="{{ asset("$theme_assets") }}/custom/img/logo/favicon/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16"
			href="{{ asset("$theme_assets") }}/custom/img/logo/favicon/favicon-16x16.png">
		<link rel="manifest" href="{{ asset("$theme_assets") }}/custom/img/logo/favicon/site.webmanifest">

		<!-- Bootstrap Css -->
		<link href='{{ asset("$theme_assets/css/bootstrap.min.css") }}' id="bootstrap-style" rel="stylesheet"
			type="text/css" />
		<!-- Icons Css -->
		<link href='{{ asset("$theme_assets/css/icons.min.css") }}' rel="stylesheet" type="text/css" />
		<!-- App Css-->
		<link href='{{ asset("$theme_assets/css/app.min.css") }}' id="app-style" rel="stylesheet" type="text/css" />
		<!-- Custom Css-->
		<link href='{{ asset("$theme_assets/custom/css/style.min.css") }}' id="customcss-style" rel="stylesheet"
			type="text/css" />

	</head>

	<body class="login-pg">
		<div class="home-btn d-none d-sm-block">
			<a href="{!! url('') !!}" class="text-dark"><i class="fas fa-home h2 sks-color-red"></i></a>
		</div>
		<div class="account-pages my-5 pt-sm-5">
			<div class="container">
				<div class="row justify-content-center">

					<div class="@yield('column_size')">
						<div class="card overflow-hidden login-card">
							{{-- <div class="bg-login text-center"> --}}
							{{-- <div class="bg-login-overlay"></div>
								<div class="position-relative"> --}}
							{{-- Banner --}}
							@yield('banner')

							<div class="card-body pt-2">
								<div class="p-2">
									<!-- Main Page -->
									@yield('content')
									<!-- End Main Page -->
								</div>
							</div>

							<div class="text-center">
								<p>@yield('log_action') </p>
								<!-- Copyright -->
								@include("$theme_dir.others.log-copyright")
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
