{{-- Extend the Layout --}}
@extends("$theme_dir.layouts.log")

@section('column_size', 'col-md-8 col-lg-6 col-xl-5')

{{-- Banner --}}
@section('banner')
	<div class="justify-content-center comp-log-logo pt-3">
		<h5 class="font-size-20 text-center">VORMIA</h5>
	</div>
@endsection

{{-- Content --}}
@section('content')
	<form action="{!! url($links->login) !!}" class="form-horizontal" method="post" accept-charset="utf-8"
		enctype="multipart/form-data" autocomplete="off">
		@csrf
		<!-- Notification -->
		{!! $notify !!}

		<div class="form-group">
			<label for="userlogname" class="sks-required">Username</label>
			<input type="text" class="form-control @error('username') is-invalid @enderror" id="logname" name="username"
				value="{{ old('username') }}" placeholder="Enter Username">
			@error('username')
				<span class="error">{{ $errors->first('username') }}</span>
			@enderror
		</div>

		<div class="form-group">
			<label for="userpassword" class="sks-required">Password</label>
			<input type="password" class="form-control @error('password') is-invalid @enderror" id="userpassword" name="password"
				value="{{ old('password') }}" placeholder="Enter password">
			@error('password')
				<span class="error">{{ $errors->first('password') }}</span>
			@enderror
		</div>

		<div class="custom-control custom-checkbox">
			<input type="checkbox" class="custom-control-input" id="remeber" name="remember" value="yes"
				@checked(old('remember', 'yes'))>
			<label class="custom-control-label" for="remeber">Remember me</label>
			<div class="col-12">
				@error('remember')
					<span class="error">{{ $errors->first('remember') }}</span>
				@enderror
			</div>
		</div>

		<div class="mt-3">
			<button class="btn btn-primary btn-block waves-effect waves-light" type="submit">Log In</button>
		</div>

		<div class="mt-4 text-center">
			<a href="#" class="text-muted"><i class="mdi mdi-lock mr-1"></i> Forgot your
				password?</a>
		</div>
	</form>
@endsection
