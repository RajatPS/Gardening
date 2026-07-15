@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1fr_0.86fr] lg:px-8 lg:py-16">
            <div class="flex flex-col justify-center">
                <p class="text-sm font-semibold uppercase text-emerald-800">Plant nursery and garden management platform</p>
                <h1 class="mt-4 max-w-4xl text-4xl font-semibold tracking-normal text-slate-950 sm:text-5xl lg:text-6xl">
                    Sell plants, manage care plans, and support customers with AI-assisted gardening.
                </h1>
                <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                    A Laravel foundation for plant commerce, gardening services, subscriptions, reminders, expert communication, AI plant help, disease detection, and staff operations.
                </p>
                @guest
                    <div class="mt-8 rounded-lg border border-emerald-100 bg-emerald-50/80 p-4 shadow-sm sm:max-w-xl">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-emerald-900">New here?</p>
                                <p class="mt-1 text-sm text-emerald-800">Create an account to save plants, manage appointments, and keep your garden plans organized.</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('customer.register') }}" class="rounded-md bg-emerald-900 px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-emerald-800">Sign up</a>
                                <a href="{{ route('customer.login') }}" class="rounded-md border border-emerald-200 bg-white px-4 py-2 text-center text-sm font-semibold text-emerald-800 transition hover:border-emerald-800 hover:text-emerald-900">Log in</a>
                            </div>
                        </div>
                    </div>
                @endguest
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('store') }}" class="rounded-md bg-emerald-900 px-5 py-3 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800">Explore Store</a>
                    <a href="{{ route('ai-tools') }}" class="rounded-md border border-slate-300 bg-white px-5 py-3 text-center text-sm font-semibold text-slate-800 transition hover:border-emerald-800 hover:text-emerald-900">View AI Tools</a>
                </div>
                <dl class="mt-10 grid max-w-2xl grid-cols-3 gap-4">
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
                        <dt class="text-xs font-medium text-slate-500">Catalog</dt>
                        <dd class="mt-1 text-2xl font-semibold text-slate-950">419+</dd>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
                        <dt class="text-xs font-medium text-slate-500">Care Plans</dt>
                        <dd class="mt-1 text-2xl font-semibold text-slate-950">3</dd>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
                        <dt class="text-xs font-medium text-slate-500">AI Modules</dt>
                        <dd class="mt-1 text-2xl font-semibold text-slate-950">5</dd>
                    </div>
                </dl>
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-slate-950 shadow-xl">
                <img
                    src="https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?auto=format&fit=crop&w=1200&q=80"
                    alt="Organized plant nursery with potted plants"
                    class="h-72 w-full object-cover sm:h-96 lg:h-[34rem]"
                >
                <div class="grid gap-3 bg-slate-950 p-5 sm:grid-cols-3">
                    <div class="rounded-md bg-white/10 p-4 text-white">
                        <p class="text-xs text-emerald-200">Next visit</p>
                        <p class="mt-1 font-semibold">Balcony audit</p>
                    </div>
                    <div class="rounded-md bg-white/10 p-4 text-white">
                        <p class="text-xs text-emerald-200">Disease alert</p>
                        <p class="mt-1 font-semibold">2 reports pending</p>
                    </div>
                    <div class="rounded-md bg-white/10 p-4 text-white">
                        <p class="text-xs text-emerald-200">Inventory</p>
                        <p class="mt-1 font-semibold">Low: soil mix</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-stone-50 py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Catalog" title="Plant and accessory store" description="Structured storefront categories for plants, pots, stands, fertilizers, pesticides, tools, and irrigation accessories." />
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $category)
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="text-lg font-semibold text-slate-950">{{ $category['name'] }}</h3>
                            <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800">{{ $category['count'] }}</span>
                        </div>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Ready for filtering by care level, price, sunlight, watering needs, pet safety, and recommended products.</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Platform Modules" title="Built around the roadmap in your plan" description="The first Laravel build maps the product roadmap into clean web sections that can later connect to models, APIs, queues, payments, AI providers, and Flutter." />
            <div class="mt-10 grid gap-5 lg:grid-cols-4">
                @foreach ([
                    ['title' => 'Commerce', 'body' => 'Products, cart, checkout, payments, order history, and saved products.'],
                    ['title' => 'Services', 'body' => 'Garden setup, inspections, pest control, soil replacement, and emergency care.'],
                    ['title' => 'Subscriptions', 'body' => 'Basic, Standard, and Premium recurring maintenance with visit schedules.'],
                    ['title' => 'Operations', 'body' => 'Staff assignments, route planner, visit reports, inventory, and admin analytics.'],
                ] as $module)
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                        <h3 class="text-base font-semibold text-slate-950">{{ $module['title'] }}</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $module['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
