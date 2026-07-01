<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VerdantOps') | Plant Nursery & Garden Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-50 text-slate-900 antialiased">
    <div class="min-h-screen">
        <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/92 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-md bg-emerald-900 text-sm font-bold text-white">VO</span>
                    <span>
                        <span class="block text-base font-semibold tracking-normal text-slate-950">VerdantOps</span>
                        <span class="block text-xs font-medium text-slate-500">Nursery, care and AI plant support</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-1 lg:flex">
                    {{-- Store dropdown --}}
                    <div class="relative">
                        <button class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-emerald-900" id="storeMenuButton">
                            Store
                            <svg class="ml-2 h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="storeMenu" class="absolute left-0 mt-2 w-48 origin-top-left rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 hidden">
                            <div class="py-1">
                                @foreach (['Plants','Medicines','Accessories','Flowering Plants','Outdoor Plants','Medicinal Plants','Bonsai'] as $cat)
                                    <a href="{{ route('store') }}?category={{ urlencode($cat) }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">{{ $cat }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('services') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-emerald-900 {{ request()->routeIs('services') ? 'bg-emerald-50 text-emerald-950' : '' }}">Services</a>
                    <a href="{{ route('subscriptions') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-emerald-900 {{ request()->routeIs('subscriptions') ? 'bg-emerald-50 text-emerald-950' : '' }}">Plans</a>
                    <a href="{{ route('ai-tools') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-emerald-900 {{ request()->routeIs('ai-tools') ? 'bg-emerald-50 text-emerald-950' : '' }}">AI Tools</a>
                    <a href="{{ route('reminders') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-emerald-900 {{ request()->routeIs('reminders') ? 'bg-emerald-50 text-emerald-950' : '' }}">Reminders</a>
                    <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-emerald-900 {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-950' : '' }}">Dashboard</a>
                    <a href="{{ route('operations') }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-emerald-900 {{ request()->routeIs('operations') ? 'bg-emerald-50 text-emerald-950' : '' }}">Operations</a>
                </nav>

                <a href="{{ route('services') }}" class="hidden rounded-md bg-emerald-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 sm:inline-flex">
                    Book Service
                </a>
            </div>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[1.4fr_repeat(3,1fr)] lg:px-8">
                <div>
                    <p class="text-lg font-semibold text-slate-950">VerdantOps</p>
                    <p class="mt-3 max-w-md text-sm leading-6 text-slate-600">
                        A Laravel platform foundation for plant commerce, service booking, subscriptions, reminders, AI plant assistance, staff operations, and admin management.
                    </p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-950">Commerce</p>
                    <p class="mt-3 text-sm text-slate-600">Plants, accessories, tools, payments, orders</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-950">Care</p>
                    <p class="mt-3 text-sm text-slate-600">Appointments, maintenance plans, health reports</p>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-950">Intelligence</p>
                    <p class="mt-3 text-sm text-slate-600">AI assistant, disease detection, AR and reminders</p>
                </div>
            </div>
        </footer>
    </div>
    <script>
        (function(){
            const btn = document.getElementById('storeMenuButton');
            const menu = document.getElementById('storeMenu');
            if (btn && menu) {
                btn.addEventListener('click', function(e){
                    e.stopPropagation();
                    menu.classList.toggle('hidden');
                });
                document.addEventListener('click', function(){
                    if (!menu.classList.contains('hidden')) menu.classList.add('hidden');
                });
            }
        })();
    </script>
</body>
</html>
