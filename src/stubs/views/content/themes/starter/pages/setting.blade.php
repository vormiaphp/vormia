<div class="for-live-wire">
	<div class="login-card bw-600">
		<div class="mb-4 text-left">
			<a href="{{ url('/') }}" class="link-icon-go-home">
				<i class="fa-solid fa-house"></i> Home
			</a>
		</div>
		<h3 class="text-center">{{ $site_name }}</h3>
		<p class="text-muted text-center">We're reading from the database by:</p>

		<!-- Notification -->
		{!! $notify !!}

		<div class="process-box">
			<ol class="text-left">
				<li>Type key on the search box 1 <code>eg. site_title</code></li>
				<li>Livewire will pull suggestions and display below using <code>SearchSetting</code></li>
				<li>Click the suggestion and edit element will open</li>
				<li>Update the entry value using <code>UpdateSetting</code></li>
				<li><strong>NB: </strong>Do not change <code>theme_name</code></li>
			</ol>
		</div>

		<div class="mb-3 text-start">
			<label for="search" class="form-label">Enter to search</label>
			<input type="text" class="form-control" id="search" wire:model="search"
				wire:keydown.debounce.1000ms="keydownSearch" autocomplete="off" placeholder="Setting configuration key"
				value="{{ old('search') }}" @error('search') is-invalid @enderror>

			@error('search')
				<span class="error"> {{ $message }}</span>
			@enderror
		</div>

		<!-- show the result -->
		<livewire:setting.search-setting />

		<!-- update settings -->
		<livewire:setting.update-setting />
	</div>
</div>
