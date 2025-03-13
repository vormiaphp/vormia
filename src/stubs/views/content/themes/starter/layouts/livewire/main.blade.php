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

		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
					integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
		<!-- NEW AASSETS -->
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

		<link
			href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200;0,300;0,400;0,800;1,300;1,400;1,800&family=Open+Sans:wght@300;400;500;600;700&display=swap"
			rel="stylesheet">
		<link
			href="https://fonts.googleapis.com/css2?family=Baloo+Chettan+2:wght@400..800&family=Dongle&family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
			rel="stylesheet">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
			integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
			crossorigin="anonymous" referrerpolicy="no-referrer" />

		<!-- Livewire -->
		@vite(['resources/css/app.css', 'resources/js/app.js'])
		@livewireStyles
		<!-- End Livewire -->
	</head>

	<body class="index-page setting-main">
		<!-- Main Page -->
		{{ $slot }}
		<!-- End Main Page -->

		<!-- Global Footer -->
		@include("$theme_dir.includes.global._footer")
		<!-- End Global Footer -->

		<!-- Main Footer -->
		@include("$theme_dir.includes.footer")
		<!-- End Main Footer -->

		<!-- Livewire Scripts -->
		@livewireScripts
		<!-- End Livewire Scripts -->
	</body>

</html>
