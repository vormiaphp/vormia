<div>
	@if ($opened)
		<form class="row g-3 mt-5" wire:submit.prevent="updateSettingValue">
			<hr />
			<div class="col-auto">
				<label for="key_title" class="pt-1">{{ $found['title'] }}</label>
			</div>

			<div class="col-auto">
				<label for="key_value" class="visually-hidden"></label>
				<input type="text" class="form-control" id="key_value" autocomplete="off" wire:model="keyvalue"
					value="{{ $found['value'] }}">
			</div>
			<div class="col-auto">
				<button type="submit" class="btn btn-primary">update</button>
			</div>
		</form>
	@endif
</div>
