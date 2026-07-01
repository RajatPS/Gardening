<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container-fluid">
        <div class="d-flex align-items-center flex-grow-1">
            <!-- Search Bar -->
            <form class="search-form" id="globalSearchForm">
                <input type="text" class="form-control" id="globalSearch" placeholder="Search...">
                <button type="submit" class="btn btn-link"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <div class="navbar-nav ms-auto d-flex align-items-center">
            <!-- Notifications -->
            <li class="nav-item dropdown">
                <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <span class="badge bg-danger rounded-pill position-absolute top-0 end-0">5</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                    <li><a class="dropdown-item" href="#">New order received</a></li>
                    <li><a class="dropdown-item" href="#">Low stock alert</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center" href="{{ route('admin.notifications.index') }}">View All</a></li>
                </ul>
            </li>

            <!-- User Dropdown -->
            <li class="nav-item dropdown ms-3">
                <a class="nav-link d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                    <div class="user-avatar me-2">
                        <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}" alt="User" class="rounded-circle">
                    </div>
                    <span class="d-none d-md-inline-block">{{ auth()->user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="{{ route('admin.settings.profile') }}"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('admin.logout') }}" class="w-100">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </li>
        </div>
    </div>
</nav>
