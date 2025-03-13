@extends("$theme_dir.layouts.$layoutName")

{{-- Content --}}
@section('content')
	<div class="row">
		<div class="col-12">
			<!-- Notification -->
			{!! $notify !!}
		</div>
	</div>

	<div class="row justify-content-center">
		<div class="col-md-8 col-sm-12">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">Add New Token</h4>
					<hr />

					<form action="{!! url($links->save) !!}" class="form-horizontal" method="post" accept-charset="utf-8"
						enctype="multipart/form-data" autocomplete="off">
						@csrf
						<div class="row" x-data="{ selectedCompany: null, selectedPartner: null }">
							<div class="col-md-6 col-sm-12" id="company">
								<div class="form-group">
									<label for="" class="form-label sks-required">Select Company</label>
									<select class="form-control form-control-md  @error('company') is-invalid @enderror" name="company"
										x-bind:disabled="selectedPartner && selectedPartner != 0"
										x-on:change="selectedCompany = $event.target.value; if (selectedCompany == 0) selectedPartner = null">
										<option @selected(old('company') == 0) value="0">- Select -</option>
										@foreach ($companies_list as $index => $list)
											<option @selected(old('company') == $list->id) value="{{ $list->id }}">
												{{ $list->name }}
											</option>
										@endforeach
									</select>
									@error('company')
										<span class="error"> {{ $errors->first('company') }} </span>
									@enderror
								</div>
							</div>

							<div class="col-md-6 col-sm-12" id="partner">
								<div class="form-group">
									<label for="" class="form-label sks-required">Select Partner</label>
									<select class="form-control form-control-md  @error('partner') is-invalid @enderror" name="partner"
										x-bind:disabled="selectedCompany && selectedCompany != 0"
										x-on:change="selectedPartner = $event.target.value; if (selectedPartner == 0) selectedCompany = null">
										<option @selected(old('partner') == 0) value="0">- Select -</option>
										@foreach ($partners_list as $index => $list)
											<option @selected(old('partner') == $list->id) value="{{ $list->id }}">
												{{ $list->name }}
											</option>
										@endforeach
									</select>
									@error('partner')
										<span class="error"> {{ $errors->first('partner') }} </span>
									@enderror
								</div>
							</div>
						</div>

						<div class="row mb-3">
							<div class="col-md-6 col-sm-12">
								<div class="form-group">
									<label for="" class="sks-required">Production URL </label>
									<input type="text" class="form-control @error('production_url') is-invalid @enderror" id=""
										placeholder="" name="production_url" value="{{ old('production_url') }}" required>

									@error('production_url')
										<span class="error">{{ $errors->first('production_url') }}</span>
									@enderror
								</div>
							</div>

							<div class="col-md-6 col-sm-12">
								<div class="form-group">
									<label for="" class="">Sandbox URL </label>
									<input type="text" class="form-control @error('sandbox_url') is-invalid @enderror" id=""
										placeholder="" name="sandbox_url" value="{{ old('sandbox_url') }}">

									@error('sandbox_url')
										<span class="error">{{ $errors->first('sandbox_url') }}</span>
									@enderror
								</div>
							</div>
						</div>

						<div class="row mb-3">
							<div class="col-md-12">
								<div class="form-group">
									<label for="" class="">Public Key <small>(generated using: openssl rsa) - <code>remeber to have
												private key in keys directory</code></small></label>
									<textarea name="public_key" id="" class="form-control @error('public_key') is-invalid @enderror" rows="10"
									 spellcheck="false">{{ old('public_key') }}</textarea>
									@error('public_key')
										<span class="error">{{ $errors->first('public_key') }}</span>
									@enderror
								</div>
							</div>
						</div>

						<div class="row">
							<ul>
								<li><code>openssl genpkey -algorithm RSA -out client_private_key.pem</code></li>
								<li><code>openssl rsa -pubout -in client_private_key.pem -out client_public_key.pem</code></li>
							</ul>
						</div>

						<div class="row">
							{{-- set col-6 and float right --}}
							<div class="col-md-5">
								<div class="form-group">
									<button type="submit" class="btn btn-success waves-effect waves-light">
										Add New Token
									</button>
								</div>
							</div>
						</div>
					</form>

				</div>
			</div>
		</div>
	</div>
@endsection
