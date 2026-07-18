<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gardening') | Plant Nursery & Garden Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-50 text-slate-900 antialiased">
    <div class="min-h-screen">
        @php
            $hideNavbar = request()->routeIs(['customer.login', 'customer.register', 'customer.google.redirect']);
        @endphp
        @if (! $hideNavbar)
        <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/92 backdrop-blur">
            <div class="mx-auto grid max-w-7xl grid-cols-[auto_1fr_auto] items-center px-4 py-3 sm:px-6 lg:px-8">
                
                <a href="{{ route('home') }}" class="flex gap-3 pl-2 flex-shrink-0">
                    <span class="grid h-10 w-10 rounded-md bg-emerald-900 text-sm font-bold text-white items-center justify-center">G</span>
                    <span class="hidden sm:block">
                        <span class="block text-base font-semibold tracking-normal text-slate-950">Gardening</span>
                        <span class="block text-xs font-medium text-slate-500">Nursery, care and AI plant support</span>
                    </span>
                </a>

                <nav class="hidden lg:flex items-center gap-1 justify-center">
                    <div class="relative">
                        <button class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-emerald-900" id="storeMenuButton">
                            Store
                            <svg class="ml-2 h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="storeMenu" class="absolute left-0 mt-2 w-48 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 hidden z-50">
                            <div class="py-1">
                                @foreach (['Plants','Medicines','Accessories','Flowering Plants','Outdoor Plants','Medicinal Plants','Bonsai'] as $cat)
                                    <a href="{{ route('store') }}?category={{ urlencode($cat) }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">{{ $cat }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('services') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-emerald-900">Services</a>
                    <a href="{{ route('subscriptions') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-emerald-900">Plans</a>
                    <a href="{{ route('ai-tools') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-emerald-900">AI Tools</a>
                    <a href="{{ route('reminders') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-emerald-900">Reminders</a>
                    <a href="{{ route('operations') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-emerald-900">Operations</a>
                </nav>

                <div class="flex items-center justify-end gap-4 flex-shrink-0">
                    <div class="relative">
                        @php
                            $authUser = auth()->user();
                            $profileLabel = auth()->check() ? ($authUser->name ?? $authUser->email ?? 'User') : 'Guest';
                            $profileInitials = 'G';
                            if (auth()->check()) {
                                $nameParts = preg_split('/\s+/', trim($profileLabel));
                                $initials = '';
                                foreach ($nameParts as $part) {
                                    if ($part !== '') {
                                        $initials .= strtoupper(substr($part, 0, 1));
                                    }
                                }
                                $profileInitials = strlen($initials) >= 2 ? substr($initials, 0, 2) : ($initials ?: 'U');
                            }
                        @endphp
                        <button id="userMenuButton" type="button" class="group flex items-center gap-2 rounded-full border border-slate-200 bg-white px-2 py-1.5 shadow-sm transition hover:border-emerald-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-500/20" aria-expanded="false" aria-haspopup="true">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full border border-emerald-200 bg-emerald-100 text-sm font-semibold text-emerald-900">
                                {{ $profileInitials }}
                            </div>
                            <span class="hidden text-sm font-semibold text-slate-700 sm:inline">
                                {{ auth()->check() ? $profileLabel : 'Account' }}
                            </span>
                            <svg class="hidden h-4 w-4 text-slate-500 transition duration-200 group-hover:text-emerald-700 sm:block" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div id="userMenu" class="absolute right-0 mt-2 z-50 hidden w-60 overflow-hidden rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                            @if (auth()->check())
                                <div class="mb-2 rounded-lg bg-slate-50 px-3 py-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Signed in as</p>
                                    <p class="truncate text-sm font-semibold text-slate-800">{{ $profileLabel }}</p>
                                </div>
                                <div class="space-y-1">
                                    <a href="{{ route('customer.orders') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-emerald-900">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M3.5 3.75A.75.75 0 014.25 3h11.5a.75.75 0 01.75.75v1.5H3.5v-1.5zm13.25 2.5H3.5v9.5a.75.75 0 00.75.75h11.5a.75.75 0 00.75-.75v-9.5zM6 8.25a.75.75 0 01.75-.75h1.5a.75.75 0 010 1.5h-1.5A.75.75 0 016 8.25zm4.5 0a.75.75 0 01.75-.75h1.5a.75.75 0 010 1.5h-1.5A.75.75 0 0110.5 8.25z"/></svg>
                                        Orders
                                    </a>
                                    <a href="{{ route('customer.cart') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-emerald-900">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M4.5 4.75A.75.75 0 015.25 4h.56a1.5 1.5 0 011.43 1.03l.89 2.47h7.05a.75.75 0 01.73 1.5h-7.7l-.6 1.6a1.5 1.5 0 01-1.43 1.03H5.25a.75.75 0 010-1.5h.56l.54-1.43L4.5 4.75zm2.75 11a1.25 1.25 0 100 2.5 1.25 1.25 0 000-2.5zm6.25 0a1.25 1.25 0 100 2.5 1.25 1.25 0 000-2.5z"/></svg>
                                        Cart
                                    </a>
                                    <a href="{{ route('customer.saved-products') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-emerald-900">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M9.53 2.97a.75.75 0 011.06 0l2.08 2.08 2.62.38a.75.75 0 01.42 1.28l-1.9 1.85.45 2.61a.75.75 0 01-1.09.79L10 10.74l-2.17 1.14a.75.75 0 01-1.09-.79l.45-2.61-1.9-1.85a.75.75 0 01.42-1.28l2.62-.38 2.08-2.08z"/></svg>
                                        Saved Products
                                    </a>
                                    <a href="{{ route('customer.profile') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-emerald-900">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2.5a3.75 3.75 0 100 7.5 3.75 3.75 0 000-7.5zm-4.75 10A2.25 2.25 0 003 14.75v.75a.75.75 0 00.75.75h12.5a.75.75 0 00.75-.75v-.75A2.25 2.25 0 0014.75 12.5H5.25z"/></svg>
                                        Profile
                                    </a>
                                    <form method="POST" action="{{ route('customer.logout') }}" class="border-t border-slate-100 pt-1">
                                        @csrf
                                        <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium text-red-600 transition hover:bg-red-50">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.5 4.75A1.75 1.75 0 015.25 3h5.5a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0V4.5h-4.5v11h4.5v-1.25a.75.75 0 011.5 0v1.5a.75.75 0 01-.75.75h-5.5A1.75 1.75 0 013.5 15.25V4.75zm10.47 2.72a.75.75 0 011.06 0l2.5 2.5a.75.75 0 010 1.06l-2.5 2.5a.75.75 0 11-1.06-1.06l1.22-1.22H8.25a.75.75 0 010-1.5h6.94l-1.22-1.22a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="space-y-1">
                                    <a href="{{ route('customer.login') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-emerald-900">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2.5a.75.75 0 01.75.75v6.69l1.72-1.72a.75.75 0 011.06 1.06l-3 3a.75.75 0 01-1.06 0l-3-3a.75.75 0 111.06-1.06l1.72 1.72V3.25A.75.75 0 0110 2.5zm-6.5 9.75a.75.75 0 01.75.75h1.5a.75.75 0 010 1.5H4.25A1.75 1.75 0 012.5 13.75v-1.5A1.75 1.75 0 014.25 10.5h1.5a.75.75 0 011.5 0v1.5a.75.75 0 01-.75.75H4.25z" clip-rule="evenodd"/></svg>
                                        Sign in
                                    </a>
                                    <a href="{{ route('customer.register') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-emerald-900">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2.5a.75.75 0 01.75.75v6.5h6.5a.75.75 0 010 1.5h-6.5v6.5a.75.75 0 01-1.5 0v-6.5H2.75a.75.75 0 010-1.5h6.5V3.25A.75.75 0 0110 2.5z"/></svg>
                                        Create account
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <button id="mobileMenuButton" class="lg:hidden p-2 text-slate-600 hover:text-emerald-900">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    </button>
                </div>
            </div>

            <div id="mobileMenu" class="hidden lg:hidden border-t border-slate-100 bg-white px-4 py-4 flex flex-col space-y-1 z-50">
                <a href="{{ route('services') }}" class="block px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 rounded-md">Services</a>
                <a href="{{ route('subscriptions') }}" class="block px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 rounded-md">Plans</a>
                <a href="{{ route('ai-tools') }}" class="block px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 rounded-md">AI Tools</a>
                <a href="{{ route('reminders') }}" class="block px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 rounded-md">Reminders</a>
                <a href="{{ route('operations') }}" class="block px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 rounded-md">Operations</a>
                <a href="{{ route('customer.cart') }}" class="block px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 rounded-md">Cart</a>
                <a href="{{ route('customer.orders') }}" class="block px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 rounded-md">Orders</a>
                <a href="{{ route('customer.saved-products') }}" class="block px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 rounded-md">Saved Products</a>
            </div>
        </header>
        @endif

        <main>@yield('content')</main>
    </div>

    <script>
        (function(){
            const menus = [
                { btn: 'storeMenuButton', menu: 'storeMenu' },
                { btn: 'userMenuButton', menu: 'userMenu' },
                { btn: 'mobileMenuButton', menu: 'mobileMenu' }
            ];

            const updateMenuState = (btn, menu, isOpen) => {
                if (!btn || !menu) {
                    return;
                }

                menu.classList.toggle('hidden', !isOpen);
                btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

                const chevron = btn.querySelector('svg');
                if (chevron) {
                    chevron.classList.toggle('rotate-180', isOpen);
                }
            };

            const closeAllMenus = () => {
                menus.forEach(item => {
                    const menu = document.getElementById(item.menu);
                    const btn = document.getElementById(item.btn);
                    updateMenuState(btn, menu, false);
                });
            };

            menus.forEach(item => {
                const btn = document.getElementById(item.btn);
                const menu = document.getElementById(item.menu);
                if (btn && menu) {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();

                        menus.forEach(other => {
                            const otherMenu = document.getElementById(other.menu);
                            const otherBtn = document.getElementById(other.btn);
                            if (otherMenu && otherBtn && other.menu !== item.menu) {
                                updateMenuState(otherBtn, otherMenu, false);
                            }
                        });

                        const shouldOpen = menu.classList.contains('hidden');
                        updateMenuState(btn, menu, shouldOpen);
                    });

                    menu.addEventListener('click', (e) => {
                        e.stopPropagation();
                    });
                }
            });

            document.addEventListener('click', () => {
                closeAllMenus();
            });
        })();
    </script>
</body>
</html>