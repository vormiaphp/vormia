<div class="page-title-box d-flex align-items-center justify-content-between">
	<h4 class="page-title mb-0 font-size-18">{{ $brand_name }}: </h4>

	<div class="page-title-right">
		<ol class="breadcrumb m-0">
			<?php
			if (!is_null($breadcrumb) && is_array($breadcrumb)) {
			    foreach ($breadcrumb as $key => $value) {
			        echo $value;
			    }
			}
			?>
		</ol>
	</div>
</div>
