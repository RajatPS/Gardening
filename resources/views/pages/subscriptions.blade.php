@extends('layouts.app')

@section('title', 'Subscriptions')

@section('content')
    <section class="bg-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Maintenance Plans" title="Subscription plans for recurring plant care" description="Basic, Standard, and Premium care plans with monthly, quarterly, and yearly billing support planned." />

            <div class="mt-10 grid gap-6 lg:grid-cols-3">
                @foreach ($plans as $plan)
                    <article class="rounded-lg border {{ $plan['name'] === 'Standard' ? 'border-emerald-800 bg-emerald-950 text-white shadow-lg' : 'border-slate-200 bg-white text-slate-950 shadow-sm' }} p-6">
                        <p class="text-sm font-semibold {{ $plan['name'] === 'Standard' ? 'text-emerald-100' : 'text-emerald-800' }}">{{ $plan['cadence'] }}</p>
                        <h2 class="mt-3 text-2xl font-semibold">{{ $plan['name'] }}</h2>
                        <p class="mt-2 text-4xl font-semibold">{{ $plan['price'] }}<span class="text-sm font-medium opacity-70">/mo</span></p>
                        <ul class="mt-6 space-y-3 text-sm">
                            @foreach ($plan['features'] as $feature)
                                <li class="flex gap-2"><span class="font-semibold">&check;</span>{{ $feature }}</li>
                            @endforeach
                        </ul>
                        <button class="mt-7 w-full rounded-md {{ $plan['name'] === 'Standard' ? 'bg-white text-emerald-950' : 'bg-emerald-900 text-white' }} px-4 py-2 text-sm font-semibold">Choose Plan</button>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
