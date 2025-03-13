@extends("$theme_dir.layouts.$layoutName")

{{-- Content --}}
@section('content')
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-body">
					<div class="row">
						<div class="col-lg-8 col-sm-12">
							<h4 class="card-title">All Tokens </h4>
							<p class="card-title-desc">
								This is a list of all token(s) registered
								<strong><code>in the system</code></strong>.
							</p>
						</div>
						<div class="col-lg-4 col-sm-12">
							<a href="{{ url($links->new) }}" class="btn btn-primary waves-effect waves-light"
								style="color:#fff; float: right;">
								<i class="bx bx-plus font-size-16 align-middle mr-2"></i> Add New
							</a>
						</div>
					</div>

					<div class="row">
						<div class="col-12">
							<!-- Notification -->
							{!! $notify !!}
						</div>
					</div>

					<table id="datatable" class="table table-bordered dt-responsive nowrap"
						style="border-collapse: collapse; border-spacing: 0; width: 100%;">
						<thead>
							<tr>
								<th></th>
								<th>Partner/Company</th>
								<th>Status</th>
								<th>Action</th>
							</tr>
						</thead>

						<tbody>
							@foreach ($token_list as $list)
								<tr>
									<td>{{ $list->id }}</td>
									<td>
										@if (!blank($list->this_partner))
											{{ $list->this_partner->name }}
										@elseif (!blank($list->this_company))
											{{ $list->this_company->name }}
										@else
											<span class="badge badge-danger">Envoked</span>
										@endif
									</td>
									<td>
										@if ($list->flag == 1)
											<span class="badge badge-success">Active</span>
										@else
											<span class="badge badge-danger">Inactive</span>
										@endif
									</td>

									<td>
										<a href="{{ url($links->route . '/edit?id=' . $list->id) }}"
											class="btn btn-primary waves-effect waves-light btn-sm">
											<i class="bx bx-spreadsheet font-size-16 align-middle mr-2"></i> Edit/View
										</a>

										<button onclick="deleteCustomer('{{ $list->id }}')"
											class="btn
                                            btn-danger waves-effect waves-light btn-sm">
											<i class="bx bx-trash font-size-16 align-middle mr-2"></i> Delete
										</button>

										@if ($list->flag == 1)
											<a href="{{ url($links->manage . '/deactivate?id=' . $list->id) }}"
												class="btn btn-info waves-effect waves-light btn-sm">
												Inactive
											</a>
										@else
											<a href="{{ url($links->manage . '/activate?id=' . $list->id) }}"
												class="btn btn-info waves-effect waves-light btn-sm">
												Active
											</a>
										@endif
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>

				</div>
			</div>
		</div>
	</div>

	<script>
		// ?  Delete Customer
		const deleteCustomer = (userId) => {
			// ? Are you sure
			Swal.fire({
				title: 'Are you sure you want to delete?',
				text: "Authorizations will be deleted permanently!",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Yes, delete it!',
				cancelButtonText: 'No, cancel!',
			}).then((result) => {
				if (result.isConfirmed) {
					// If confirmed, send a request to the Laravel route for deletion
					let base_url = `{{ url($links->delete) }}`;
					window.location.href = `${base_url}?id=${userId}`;
				} else {
					// If canceled, close the SweetAlert dialog
					showConfirm = false;
				}
			});
		}
	</script>
@endsection
