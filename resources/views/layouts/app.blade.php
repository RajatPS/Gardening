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
                        <button id="userMenuButton" class="flex items-center gap-2 rounded-full">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full border border-emerald-200 bg-emerald-100 text-xs font-bold text-emerald-900">
                                {{ $profileInitials }}
                            </div>
                            @if (!auth()->check())
                                <span class="hidden text-sm font-medium text-slate-700 sm:inline">Guest</span>
                            @endif
                        </button>
                        <div id="userMenu" class="absolute right-0 mt-2 w-48 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 hidden z-50">
                            <a href="{{ route('customer.orders') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Orders</a>
                            <a href="{{ route('customer.cart') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cart</a>
                            <a href="{{ route('customer.saved-products') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Saved Products</a>
                            <a href="{{ route('customer.profile') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Profile</a>
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

            menus.forEach(item => {
                const btn = document.getElementById(item.btn);
                const menu = document.getElementById(item.menu);
                if (btn && menu) {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        // Hide other menus when opening a new one
                        menus.forEach(other => {
                            if (other.menu !== item.menu) document.getElementById(other.menu).classList.add('hidden');
                        });
                        menu.classList.toggle('hidden');
                    });
                }
            });

            document.addEventListener('click', () => {
                menus.forEach(item => {
                    document.getElementById(item.menu).classList.add('hidden');
                });
            });
        })();
    </script>
</body>
</html>