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
								<h4 class="card-title">Continent Info</h4>
								<hr />
								{{-- <p class="card-title-desc"></p> --}}
								<div>
									<div class="row">
										<div class="col-md-12 col-sm-12">
											<div class="form-group">
												<label for="" class="sks-required">
													Continent Name
												</label>
												<input type="text" class="form-control @error('name') is-invalid @enderror" id="" placeholder=""
													name="name" value="{{ $resultFound->name }}">

												@error('name')
													<span class="error">{{ $errors->first('name') }}</span>
												@enderror
											</div>
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
									Update Continent
								</button>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</form>
@endsection
