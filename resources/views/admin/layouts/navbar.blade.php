<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container-fluid">
        <div class="d-flex align-items-center flex-grow-1">
            <!-- Search Bar -->
            <form class="search-form" id="globalSearchForm">
                <input type="text" class="form-control" id="globalSearch" placeholder="Search...">
                <button type="submit" class="btn btn-link"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <ul class="navbar-nav ms-auto d-flex align-items-center flex-row">
            <!-- Notifications -->
            @php
                $notificationItems = collect();
                $ordersCount = DB::table('orders')->count();
                if ($ordersCount === 0) {
                    $notificationItems->push(['title' => 'No orders yet', 'link' => route('admin.orders.index'), 'icon' => 'fas fa-shopping-cart']);
                }

                $productsCount = App\Models\Product::count();
                if ($productsCount === 0) {
                    $notificationItems->push(['title' => 'No products available yet', 'link' => route('admin.products.index'), 'icon' => 'fas fa-box']);
                }

                $staffCount = App\Models\User::where('role', 'staff')->count();
                if ($staffCount === 0) {
                    $notificationItems->push(['title' => 'No staff members added yet', 'link' => route('admin.staff.index'), 'icon' => 'fas fa-user-tie']);
                }

                $appointmentsCount = App\Models\ServiceBooking::count();
                if ($appointmentsCount === 0) {
                    $notificationItems->push(['title' => 'No appointments scheduled yet', 'link' => route('admin.appointments.index'), 'icon' => 'fas fa-calendar-check']);
                }

                if ($notificationItems->isEmpty()) {
                    $notificationItems->push(['title' => 'Everything looks up to date', 'link' => route('admin.dashboard'), 'icon' => 'fas fa-check-circle']);
                }
            @endphp
            <li class="nav-item dropdown">
                <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    @if($notificationItems->count() > 0)
                        <span class="badge bg-danger rounded-pill position-absolute top-0 end-0">{{ $notificationItems->count() }}</span>
                    @endif
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                    @foreach($notificationItems as $notification)
                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ $notification['link'] }}">
                                <i class="{{ $notification['icon'] }} me-2"></i>
                                <span>{{ $notification['title'] }}</span>
                            </a>
                        </li>
                    @endforeach
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center" href="{{ route('admin.notifications.index') }}">View All</a></li>
                </ul>
            </li>

            <!-- User Dropdown -->
            <li class="nav-item dropdown ms-3">
                <a class="nav-link d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                    <div class="user-avatar me-2">
                        <img src="https://ui-avatars.com/api/?name={{ $currentUser?->name ?? 'Admin' }}" alt="User" class="rounded-circle">
                    </div>
                    <span class="d-none d-md-inline-block">{{ $currentUser?->name ?? 'Admin' }}</span>
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
        </ul>
    </div>
</nav>
