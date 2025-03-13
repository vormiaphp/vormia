@extends("$theme_dir.layouts.$layoutName")

{{-- Content --}}
@section('content')
	<form action="{!! url($links->update) !!}" class="form-horizontal" method="post" accept-charset="utf-8"
		enctype="multipart/form-data" autocomplete="off">
		@csrf

		{{-- hidden userId --}}
		<input type="hidden" name="user_id" value="{{ $resultFound->id }}">

		<!-- Notification -->
		{!! $notify !!}

		<div class="row">
			<div class="col-lg-8 col-sm-12">
				<div class="card">
					<div class="card-body">
						<h4 class="card-title">Personal Info</h4>
						<hr />
						{{-- <p class="card-title-desc"></p> --}}
						<div>
							<div class="row">
								<div class="col-md-4 col-sm-12">
									<div class="form-group">
										<label for="" class="sks-required">
											Full Name
										</label>
										<input type="text" class="form-control @error('name') is-invalid @enderror" id="" placeholder=""
											name="name" value="{{ $resultFound->name }}">

										@error('name')
											<span class="error">{{ $errors->first('name') }}</span>
										@enderror
									</div>
								</div>

								<div class="col-md-4 col-sm-12">
									<div class="form-group">
										<label for="" class="sks-required">
											Email
										</label>
										<input type="email" class="form-control @error('email') is-invalid @enderror" id="" placeholder=""
											name="email" value="{{ $resultFound->email }}">

										@error('email')
											<span class="error">{{ $errors->first('email') }}</span>
										@enderror
									</div>
								</div>
								<div class="col-md-4 col-sm-12">
									<div class="form-group">
										<label for="" class="">
											Phone Number <small>(start with 254 )</small>
										</label>
										<input type="text" class="form-control @error('phone') is-invalid @enderror" id="" placeholder=""
											name="phone" value="{{ $resultFound->phone }}">

										@error('phone')
											<span class="error">{{ $errors->first('phone') }}</span>
										@enderror
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-4 col-sm-12">
									<div class="form-group">
										<label for="" class="sks-required">Access Level </label>
										<select class="form-control select2 @error('access') is-invalid @enderror" name="access[]" multiple>
											<option value="0"> -- Pick Level -- </option>
											@php
												$role = $resultFound->roles->pluck('id')->toArray();
											@endphp
											@foreach ($main_roles as $list)
												<option value="{{ $list->id }}" @selected(in_array($list->id, $role))>
													{{ $list->name }}
												</option>
											@endforeach
										</select>
										@error('access')
											<span class="error">{{ $errors->first('access') }}</span>
										@enderror
									</div>
								</div>

								<div class="col-md-8 col-sm-12">
									<div class="form-group">
										<label for="" class="">
											Note <small>(any extra infomantion)</small>
										</label>

										@php $note = $resultFound->usermetas->where('key', 'note')->first()?->value; @endphp
										<input type="text" class="form-control @error('note') is-invalid @enderror" id="" placeholder=""
											name="note" value="{{ $note }}">

										@error('note')
											<span class="error">{{ $errors->first('note') }}</span>
										@enderror
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-lg-4 col-sm-12">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-md-12 col-sm-12">
								<div class="form-group">
									<label for="" class="sks-required">
										Account Username
									</label>
									<input type="text" class="form-control" name="" value="{{ $resultFound->username }}" disabled>
								</div>
							</div>

							<div class="col-md-12 col-sm-12">
								<div class="form-group">
									<label for="" class="sks-required">
										Create Password
									</label>
									<input type="password" class="form-control @error('password') is-invalid @enderror" id=""
										placeholder="Enter password" name="password" value="{{ old('password') }}">

									@error('password')
										<span class="error">{{ $errors->first('password') }}</span>
									@enderror
								</div>
							</div>

							<div class="col-md-12 col-sm-12">
								<div class="form-group">
									<label for="" class="sks-required">
										Confirm Password
									</label>
									<input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id=""
										placeholder="Enter confirm password" name="password_confirmation"
										value="{{ old('password_confirmation') }}">

									@error('password_confirmation')
										<span class="error">{{ $errors->first('password_confirmation') }}</span>
									@enderror
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			{{-- set col-6 and float right --}}
			<div class="col-6">
				<div class=" float-end">
					<div class="form-group">
						<button type="submit" class="btn btn-success waves-effect waves-light"> Update
							User Info</button>
					</div>
				</div>
			</div>
		</div>
	</form>
@endsection
