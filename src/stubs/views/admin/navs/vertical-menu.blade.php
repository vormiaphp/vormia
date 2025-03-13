  <div class="user-wid text-center py-4">
      <div class="user-img">
          <img src='{{ asset("$userinfo->profile") }}' alt="" class="avatar-md mx-auto rounded-circle" />
      </div>

      <div class="mt-3">
          <a href="#" class="text-dark font-weight-medium font-size-16"><?= $userinfo->name ?></a>
          <p class="text-body mt-1 mb-0 font-size-13">VORMIA</p>
      </div>
  </div>

  <!--- Sidemenu -->
  <div id="sidebar-menu">
      <!-- Left Menu Start -->
      <ul class="metismenu list-unstyled" id="side-menu">
          <li class="menu-title">Defaults</li>
          <li>
              <a href='{{ url('/') }}' class="waves-effect" target="_blank">
                  <i class="fas fa-external-link-alt"></i>
                  <span>View Site</span>
              </a>
          </li>
          <li class="menu-title">Menu</li>
          <!-- Menus -->
          @include("$theme_dir.navs.sidebar-menu")
          <!-- End Menus -->
      </ul>
  </div>
  <!-- Sidebar -->
