<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <h4 class="mb-0">GardenHub</h4>
            <p class="text-muted small">Admin Panel</p>
        </div>
        <button class="sidebar-toggle btn btn-link" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>

        <!-- User Management -->
        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span>User Management</span>
        </a>

        <!-- Staff Management -->
        <a href="{{ route('admin.staff.index') }}" class="nav-link {{ request()->routeIs('admin.staff.*') ? 'active' : '' }}">
            <i class="fas fa-user-tie"></i>
            <span>Staff Management</span>
        </a>

        <!-- Product Management -->
        <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
            <i class="fas fa-box"></i>
            <span>Product Management</span>
        </a>

        <!-- Order Management -->
        <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
            <i class="fas fa-shopping-cart"></i>
            <span>Order Management</span>
        </a>

        <!-- Appointment Management -->
        <a href="{{ route('admin.appointments.index') }}" class="nav-link {{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}">
            <i class="fas fa-calendar-check"></i>
            <span>Appointments</span>
        </a>

        <!-- Subscription Management -->
        <a href="{{ route('admin.subscriptions.index') }}" class="nav-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
            <i class="fas fa-sync"></i>
            <span>Subscriptions</span>
        </a>

        <!-- Transaction Records -->
        <a href="{{ route('admin.transactions.index') }}" class="nav-link {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
            <i class="fas fa-credit-card"></i>
            <span>Transactions</span>
        </a>

        <!-- Inventory Management -->
        <a href="{{ route('admin.inventory.index') }}" class="nav-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
            <i class="fas fa-warehouse"></i>
            <span>Inventory</span>
        </a>

        <!-- Notifications -->
        <a href="{{ route('admin.notifications.index') }}" class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
            <i class="fas fa-bell"></i>
            <span>Notifications</span>
        </a>

        <!-- Reports -->
        <div class="nav-section">
            <p class="nav-section-title">Reports</p>
        </div>
        <a href="{{ route('admin.reports.revenue') }}" class="nav-link {{ request()->routeIs('admin.reports.revenue') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i>
            <span>Revenue</span>
        </a>
        <a href="{{ route('admin.reports.sales') }}" class="nav-link {{ request()->routeIs('admin.reports.sales') ? 'active' : '' }}">
            <i class="fas fa-chart-pie"></i>
            <span>Sales</span>
        </a>
        <a href="{{ route('admin.reports.users') }}" class="nav-link {{ request()->routeIs('admin.reports.users') ? 'active' : '' }}">
            <i class="fas fa-chart-area"></i>
            <span>User Reports</span>
        </a>
        <a href="{{ route('admin.reports.staff-performance') }}" class="nav-link {{ request()->routeIs('admin.reports.staff-performance') ? 'active' : '' }}">
            <i class="fas fa-tasks"></i>
            <span>Staff Performance</span>
        </a>

        <!-- Audit Logs -->
        <a href="{{ route('admin.audit-logs.index') }}" class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
            <i class="fas fa-history"></i>
            <span>Audit Logs</span>
        </a>

        <a href="{{ route('admin.garden-setups.index') }}" class="nav-link {{ request()->routeIs('admin.garden-setups.*') ? 'active' : '' }}">
            <i class="fas fa-seedling"></i>
            <span>Garden Setups</span>
        </a>

        <!-- Settings -->
        <div class="nav-section">
            <p class="nav-section-title">Settings</p>
        </div>
        <a href="{{ route('admin.settings.general') }}" class="nav-link {{ request()->routeIs('admin.settings.general') ? 'active' : '' }}">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
        <a href="{{ route('admin.settings.profile') }}" class="nav-link {{ request()->routeIs('admin.settings.profile') ? 'active' : '' }}">
            <i class="fas fa-user-circle"></i>
            <span>Profile</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="btn btn-link text-danger w-100">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
