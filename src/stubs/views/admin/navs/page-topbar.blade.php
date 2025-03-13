<div class="float-right">

	<div class="dropdown d-inline-block">
		<button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown" data-toggle="dropdown"
			aria-haspopup="true" aria-expanded="false">
			<img class="rounded-circle header-profile-user" src='{{ asset("$userinfo->profile") }}' alt="Header Avatar" />
			<span class="d-none d-xl-inline-block ml-1"><?= $userinfo->name ?></span>
			<i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
		</button>
		<div class="dropdown-menu dropdown-menu-right">
			<!-- item-->
			<a class="dropdown-item" href="#">
				<i class="bx bx-user font-size-16 align-middle mr-1"></i> Profile
			</a>
			<div class="dropdown-divider"></div>
			<a class="dropdown-item text-danger" href='{{ url('vrm-admin/logout') }}'>
				<i class="bx bx-power-off font-size-16 align-middle mr-1 text-danger"></i> Logout
			</a>
		</div>
	</div>
</div>

<div>
	<!-- LOGO -->
	<div class="navbar-brand-box">
		<a href='#' class="logo logo-dark">
			<span class="logo-sm">
				<img src='{{ asset("$theme_assets/custom/img/logo/logo-blue.png") }}' alt="" height="55" />
			</span>
			<span class="logo-lg">
				<img src='{{ asset("$theme_assets/custom/img/logo/logo-blue.png") }}' alt="" height="60" />
			</span>
		</a>

		<a href="#" class="logo logo-light">
			<span class="logo-sm">
				<img src='{{ asset("$theme_assets/custom/img/logo/logo-blue.png") }}' alt="" height="55" />
			</span>
			<span class="logo-lg">
				<img src='{{ asset("$theme_assets/custom/img/logo/logo-blue.png") }}' alt="" height="60" />
			</span>
		</a>
	</div>

	<button type="button" class="btn btn-sm px-3 font-size-16 header-item toggle-btn waves-effect" id="vertical-menu-btn">
		<i class="fa fa-fw fa-bars"></i>
	</button>
</div>
