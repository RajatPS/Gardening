@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<div class="mx-auto max-w-3xl text-center">
    @if ($eyebrow)
        <p class="text-sm font-semibold uppercase text-emerald-800">{{ $eyebrow }}</p>
    @endif
    <h2 class="mt-2 text-3xl font-semibold tracking-normal text-slate-950 sm:text-4xl">{{ $title }}</h2>
    @if ($description)
        <p class="mt-4 text-base leading-7 text-slate-600">{{ $description }}</p>
    @endif
</div>
