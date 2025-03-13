<div>
	@if (!empty($results) && !is_null($results))
		<div class="result-box">
			<div class="result-box-body">
				@foreach ($results as $result)
					<div class="result-box-body-item">
						<a href="#" wire:click.prevent="..." wire:click="editSetting({{ $result->id }})">
							<span>
								<code>{{ $result->title }}</code> {{ $result->value }}
							</span>
						</a>
					</div>
				@endforeach
			</div>
		</div>
	@endif
</div>
