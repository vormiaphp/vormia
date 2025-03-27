<!DOCTYPE html>
<html>

	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		@if ($subject)
			<title>{{ ucwords($subject) }}</title>
		@else
			<title>Mail</title>
		@endif
		<style>
			body {
				font-family: Arial, sans-serif;
				background-color: #f6f6f6;
				margin: 0;
				padding: 0;
			}

			.container {
				max-width: 600px;
				margin: 30px auto;
				background: #fff;
				padding: 20px;
				border-radius: 8px;
				box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
			}

			.header {
				font-size: 22px;
				font-weight: bold;
				color: #333;
			}

			.sub-header {
				font-size: 16px;
				color: #555;
				margin: 15px 0;
			}

			.box {
				background: #f0f4f9;
				margin-top: 20px;
				margin-bottom: 20px;
				padding: 25px 15px;
				border-radius: 5px;
				font-size: 14px;
				color: #333;
			}

			.button {
				display: inline-block;
				background: #1a73e8;
				color: white;
				padding: 13px 40px;
				text-decoration: none;
				border-radius: 20px;
				font-size: 14px;
				margin-top: 30px;
			}

			.footer {
				font-size: 12px;
				color: #777;
				margin-top: 20px;
				border-top: 1px solid #ddd;
				padding-top: 10px;
			}

			.unsubscribe {
				color: #1a73e8;
				text-decoration: none;
			}
		</style>
	</head>

	<body>
		<div class="container">
			@if (!is_null($logo))
				<img src="{!! $logo !!}" alt="{{ $subject }}" width="70" />
			@endif
