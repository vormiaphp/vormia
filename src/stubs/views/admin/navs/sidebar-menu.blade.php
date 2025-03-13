@php
	use App\Http\Middleware\CheckRolePermission;
	$checkRolePermission = new CheckRolePermission();
@endphp

<li>
	<a href='{{ url('vrm/dashboard') }}' class="waves-effect">
		<i class="mdi mdi-airplay"></i>
		<span>Dashboard</span>
	</a>
</li>
<hr />

@if (Auth::check() && $checkRolePermission->hasPermission(Auth::user(), 'setup'))
	<!-- Setup -->
	<li>
		<a href="javascript: void(0);" class="has-arrow waves-effect">
			<i class="mdi mdi-server-network"></i>
			<span>Setup</span>
		</a>
		<ul class="sub-menu" aria-expanded="false">
			<li><a href='{{ url('vrm/setup/continent') }}'>Continent</a></li>
			<li><a href='{{ url('vrm/setup/currency') }}'>Currency</a></li>
		</ul>
	</li>

	<hr />
@endif

<!-- SETTINGS -->
@if (Auth::check() && $checkRolePermission->hasPermission(Auth::user(), 'permissions'))
	<li>
		<a href="javascript: void(0);" class="has-arrow waves-effect">
			<i class="mdi mdi-tools"></i>
			<span>Settings</span>
		</a>
		<ul class="sub-menu" aria-expanded="false">
			<li><a href='{{ url('vrm/roles') }}'>Roles</a></li>
			@if ($checkRolePermission->hasPermission(Auth::user(), 'users'))
				<li><a href='{{ url('vrm/users') }}'>Users</a></li>
			@endif
		</ul>
	</li>
	<hr />
@endif

<li>
	<a href='{{ url('vrm-admin/logout') }}' class="waves-effect sks-color-red">
		<i class="mdi mdi-login-variant sks-color-red"></i>
		<span>Logout</span>
	</a>
</li>
