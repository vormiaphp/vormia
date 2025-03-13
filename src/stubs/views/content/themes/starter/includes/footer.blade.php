<script src="{{ asset($plugin_assets) }}/datepicker/js/datepicker-full.min.js"></script>

<input type="hidden" id="base_url_link" value="{{ url('/') }}">

<script src='{{ asset("$theme_assets") }}/js/demo.js'></script>

<script type="text/javascript">
	// Datepicker
	const picker = document.querySelectorAll('.date');
	if (picker) {
		// Loop through each picker
		picker.forEach(function(picked) {
			// Create a new datepicker
			const datepicker = new Datepicker(picked, {
				minDate: new Date(),
				autohide: true,
				format: 'dd/mm/yyyy',
				clearBtn: true,
			});
		});
	}
</script>
