@extends("$theme_dir.layouts.$layoutName")

{{-- Content --}}
@section('content')
	<form action="{!! url($links->update) !!}" class="form-horizontal" method="post" accept-charset="utf-8"
		enctype="multipart/form-data" autocomplete="off">
		@csrf

		{{-- hidden userId --}}
		<input type="hidden" name="id" value="{{ $resultFound->id }}">

		<!-- Notification -->
		{!! $notify !!}

		<div class="row justify-content-center">
			<div class="col-md-7 col-sm-12">

				<div class="row">
					<div class="col-lg-12 col-sm-12">
						<div class="card">
							<div class="card-body">
								<h4 class="card-title">Role Info</h4>
								<hr />

								<div class="row mb-2">
									<div class="col-lg-8 col-sm-12">
										<div class="form-group">
											<label for="" class="sks-required">
												Name
											</label>
											<input type="text" class="form-control @error('name') is-invalid @enderror" id="" placeholder=""
												name="name" value="{{ $resultFound->name }}">

											@error('name')
												<span class="error">{{ $errors->first('name') }}</span>
											@enderror
										</div>
									</div>
									<div class="col-lg-4 col-sm-12">
										<div class="form-group">
											<label for="" class="sks-required">
												Authority
											</label>
											<input type="text" class="form-control @error('authority') is-invalid @enderror" name="authority"
												value="{{ $resultFound->authority }}">

											@error('authority')
												<span class="error">{{ $errors->first('authority') }}</span>
											@enderror
										</div>
									</div>
								</div>

								<div class="row">
									<div class="col-md-12 col-sm-12">
										<div class="form-group">
											<label for="" class="sks-required">
												Module <small>(comma separated)</small>
											</label>
											<textarea name="module" id="" class="form-control @error('module') is-invalid @enderror" rows="5"
											 spellcheck="false">{{ $resultFound->module }}</textarea>
											@error('module')
												<span class="error">{{ $errors->first('module') }}</span>
											@enderror
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-6">
						<div class=" float-end">
							<div class="form-group">
								<button type="submit" class="btn btn-success waves-effect waves-light">
									Update Role
								</button>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</form>
@endsection
