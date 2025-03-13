<!DOCTYPE html>
<!--[if IE 9 ]><html class="ie9"><![endif]-->
<html lang="en">

	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<!-- Global Header -->
		@include("$theme_dir.includes.global._head")
		<!-- End Global Header -->

		<!-- Main Header -->
		@include("$theme_dir.includes.head")
		<!-- End Main Header -->
	</head>

	<body>

		<!-- Main Page -->
		@yield('content')
		<!-- End Main Page -->

		<!-- Global Footer -->
		@include("$theme_dir.includes.global._footer")
		<!-- End Global Footer -->

		<!-- Main Footer -->
		@include("$theme_dir.includes.footer")
		<!-- End Main Footer -->
	</body>

</html>
