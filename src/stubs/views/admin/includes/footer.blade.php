<!-- JAVASCRIPT -->
<script src='{{ asset("$theme_assets/libs/jquery/jquery.min.js") }}'></script>
<script src='{{ asset("$theme_assets/libs/bootstrap/js/bootstrap.bundle.min.js") }}'></script>
<script src='{{ asset("$theme_assets/libs/metismenu/metisMenu.min.js") }}'></script>
<script src='{{ asset("$theme_assets/libs/simplebar/simplebar.min.js") }}'></script>
<script src='{{ asset("$theme_assets/libs/node-waves/waves.min.js") }}'></script>

<script src='{{ asset("$theme_assets/js/app.js") }}'></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.3/dist/sweetalert2.all.min.js"></script>

<!-- DatePicker -->
<script src="{{ asset($plugin_assets) }}/datepicker/js/datepicker-full.min.js"></script>
<!-- Custom Js-->
<script src="{{ asset("$theme_assets/custom/js/custom.js") }}?v={{ time() }}"></script>

<script type="text/javascript">
	// Datepicker
	const picker = document.querySelectorAll('.date');
	if (picker) {
		// Loop through each picker
		picker.forEach(function(picked) {
			// Create a new datepicker
			const datepicker = new Datepicker(picked, {
				autohide: true,
				format: 'dd/mm/yyyy',
				clearBtn: true,
			});
		});
	}

	// Select
	$(document).ready(function() {
		$(".select2").select2();
		$(".select-auto").select2();
		$(".select-sale").select2();
	});
</script>
