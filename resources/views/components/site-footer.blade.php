<footer class="border-t border-slate-200 bg-slate-50/90">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[1.2fr_0.8fr_0.8fr]">
            <div>
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 items-center justify-center rounded-md bg-emerald-900 text-sm font-bold text-white">G</span>
                    <div>
                        <p class="text-base font-semibold tracking-normal text-slate-950">Gardening</p>
                        <p class="text-sm text-slate-500">Nursery, care and AI plant support</p>
                    </div>
                </div>
                <p class="mt-4 max-w-md text-sm leading-7 text-slate-600">
                    A modern plant commerce experience for browsing products, planning care, and staying supported with thoughtful guidance.
                </p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="https://www.instagram.com" target="_blank" rel="noreferrer" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700" aria-label="Instagram">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7.5 2h9A5.5 5.5 0 0122 7.5v9A5.5 5.5 0 0116.5 22h-9A5.5 5.5 0 012 16.5v-9A5.5 5.5 0 017.5 2zm0 2A3.5 3.5 0 004 7.5v9A3.5 3.5 0 007.5 20h9a3.5 3.5 0 003.5-3.5v-9A3.5 3.5 0 0016.5 4h-9zm4.5 3.2a4.3 4.3 0 104.3 4.3 4.3 4.3 0 00-4.3-4.3zm0 2a2.3 2.3 0 11-2.3 2.3A2.3 2.3 0 0112 9.2zm5.4-1.2a1 1 0 101 1 1 1 0 00-1-1z"/></svg>
                    </a>
                    <a href="https://www.facebook.com" target="_blank" rel="noreferrer" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700" aria-label="Facebook">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 22v-8h2.7l.4-3h-3.1V4.6c0-.9.2-1.5 1.5-1.5h1.6V.2c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.2H8v3h2.3v8h3.2z"/></svg>
                    </a>
                    <a href="https://www.linkedin.com" target="_blank" rel="noreferrer" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700" aria-label="LinkedIn">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.94 8.5A1.56 1.56 0 106.94 5.4a1.56 1.56 0 000 3.1zM5.5 9.8h2.88V18H5.5zM10.4 9.8h2.76v1.15h.04c.38-.72 1.32-1.48 2.72-1.48 2.91 0 3.44 1.91 3.44 4.4V18H16.4v-7.3c0-1.74-.03-3.98-2.42-3.98-2.43 0-2.8 1.9-2.8 3.86V18H10.4z"/></svg>
                    </a>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Quick links</h3>
                <ul class="mt-4 space-y-3 text-sm text-slate-600">
                    <li><a href="{{ route('home') }}" class="transition hover:text-emerald-700">Home</a></li>
                    <li><a href="{{ route('store') }}" class="transition hover:text-emerald-700">Products</a></li>
                    <li><a href="{{ route('store') }}" class="transition hover:text-emerald-700">Categories</a></li>
                    <li><a href="{{ route('customer.saved-products') }}" class="transition hover:text-emerald-700">Saved Products</a></li>
                    <li><a href="{{ route('customer.cart') }}" class="transition hover:text-emerald-700">Cart</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Support</h3>
                <ul class="mt-4 space-y-3 text-sm text-slate-600">
                    <li><a href="mailto:support@gardening.app" class="transition hover:text-emerald-700">Contact</a></li>
                    <li><a href="mailto:support@gardening.app" class="transition hover:text-emerald-700">Customer Support</a></li>
                    <li><a href="{{ route('home') }}" class="transition hover:text-emerald-700">Privacy Policy</a></li>
                    <li><a href="{{ route('home') }}" class="transition hover:text-emerald-700">Terms &amp; Conditions</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-3 border-t border-slate-200 pt-6 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <p>© 2026 Gardening. All rights reserved.</p>
            <p>Made with <span class="text-rose-500">♥</span> for plant lovers</p>
        </div>
    </div>
</footer>
