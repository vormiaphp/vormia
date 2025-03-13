@extends("$theme_dir.layouts.$layoutName")

{{-- Content --}}
@section('content')
	<div class="row justify-content-center">
		<div class="col-md-8 col-sm-12">

			<div class="row">
				<div class="col-lg-10 col-sm-12">
					<div class="card">
						<div class="card-body">
							<h4 class="card-title">Edit Token</h4>
							<!-- Notification -->
							{!! $notify !!}

							<hr />
							<div class="row">
								<div class="col-md-6 col-sm-12">
									<div class="form-group">
										<label for="" class="form-label">Select Company</label>
										@php
											$company = !blank($resultFound->this_company) ? $resultFound->this_company->name : 'N/A';
										@endphp
										<input type="text" class="form-control" id="" placeholder="" name=""
											value="{{ $company }}" disabled>
									</div>
								</div>

								<div class="col-md-6 col-sm-12">
									<div class="form-group">
										<label for="" class="form-label">Select Partner</label>
										@php
											$partner = !blank($resultFound->this_partner) ? $resultFound->this_partner->name : 'N/A';
										@endphp
										<input type="text" class="form-control" id="" placeholder="" name=""
											value="{{ $partner }}" disabled>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-12 col-sm-12">
									<div class="form-group">
										<label for="" class="form-label">Production Token</label>
										<input type="text" class="form-control" id="" placeholder="" name=""
											value="{{ $resultFound->production_token }}">
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-12 col-sm-12">
									<div class="form-group">
										<label for="" class="form-label">Sandbox Token</label>
										<input type="text" class="form-control" id="" placeholder="" name="status"
											value="{{ $resultFound->sandbox_token }}">
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-6 col-sm-12">
									<div class="form-group">
										<label for="" class="form-label">Production URL</label>
										<input type="text" class="form-control" id="" placeholder="" name=""
											value="{{ $resultFound->production_url }}">
									</div>
								</div>

								<div class="col-md-6 col-sm-12">
									<div class="form-group">
										<label for="" class="form-label">Sandbox URL</label>
										<input type="text" class="form-control" id="" placeholder="" name="status"
											value="{{ $resultFound->sandbox_url }}">
									</div>
								</div>
							</div>

							<div class="row d-none">
								<div class="col-md-12 col-sm-12">
									<div class="form-group">
										<label for="" class="">Public Key </label>
										@php
											$public_key = !blank($resultFound->public_key) ? $resultFound->public_key : null;
											if (!blank($public_key)) {
											    $public_key = json_decode($public_key, true);
											    // ? html stripslashes
											    $public_key = trim(stripslashes($public_key));
											}
										@endphp
										<textarea name="note" id="" class="form-control" rows="10" spellcheck="false">
                                            {{ $public_key }}
                                        </textarea>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
@endsection
