@extends('layouts.app')

@section('title', 'Reminders')

@section('content')
    <section class="bg-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Smart Reminders" title="Watering, fertilizer, pruning and service calendars" description="Reminder workflow for daily, weekly, and monthly calendar views with email, SMS, push, in-app alerts, snooze, completion, and custom alarm sounds." />

            <div class="mt-10 grid gap-6 lg:grid-cols-[0.8fr_1fr]">
                <div class="rounded-lg border border-slate-200 bg-stone-50 p-6">
                    <h2 class="text-lg font-semibold text-slate-950">Upcoming reminders</h2>
                    <div class="mt-5 space-y-3">
                        @foreach ($reminders as $reminder)
                            <div class="rounded-md border border-slate-200 bg-white p-4">
                                <p class="text-sm font-semibold text-slate-950">{{ $reminder['task'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $reminder['time'] }} · {{ $reminder['type'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-950">Calendar controls</h2>
                    <form method="POST" action="{{ route('reminders.create') }}" class="mt-6">
                        @csrf
                        <div class="grid gap-3">
                            <input name="task" required placeholder="Reminder task (e.g. Water Monstera)" class="rounded-md border p-2">
                            <input name="remind_at" required type="datetime-local" class="rounded-md border p-2">
                            <select name="type" class="rounded-md border p-2">
                                <option value="Watering">Watering</option>
                                <option value="Fertilizer">Fertilizer</option>
                                <option value="Medicine">Medicine</option>
                                <option value="Service">Service</option>
                                <option value="Other">Other</option>
                            </select>
                            <select name="frequency" class="rounded-md border p-2">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="every_3_months">Every 3 months</option>
                            </select>
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" name="plant_care" value="1" checked>
                                Create recurring plant-care reminders
                            </label>
                            <div class="flex gap-2">
                                <button class="rounded-md bg-emerald-900 px-4 py-2 text-white">Schedule Reminder</button>
                                <button type="button" id="testAlarm" class="rounded-md border px-4 py-2">Test Alarm</button>
                            </div>
                        </div>
                    </form>
                    <div class="mt-5 grid gap-3 sm:grid-cols-3">
                        @foreach (['Daily View', 'Weekly View', 'Monthly View'] as $view)
                            <button class="rounded-md border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 hover:border-emerald-800 hover:text-emerald-900">{{ $view }}</button>
                        @endforeach
                    </div>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        @foreach (['Silent', 'Default Tone', 'Nature Sounds', 'Custom Uploaded Sound'] as $sound)
                            <label class="flex items-center gap-3 rounded-md bg-stone-50 p-3 text-sm text-slate-700">
                                <input type="radio" name="sound" class="accent-emerald-900">
                                {{ $sound }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        (function(){
            const reminders = @json($reminders);
            const audio = new Audio('data:audio/wav;base64,UklGRiQAAABXQVZFZm10IBAAAAABAAEAESsAACJWAAACABAAZGF0YQAAAAA=');
            function parseTimeText(t){
                // Try simple parse; if fails, return null
                try{ const d = new Date(t); if(!isNaN(d)) return d; }catch(e){}
                // support 'Today, 7:30 PM'
                if(t && t.includes('Today')){
                    const parts = t.split(',').pop().trim();
                    const today = new Date();
                    const dt = new Date(today.toDateString() + ' ' + parts);
                    if(!isNaN(dt)) return dt;
                }
                return null;
            }

            reminders.forEach(r => {
                const dt = parseTimeText(r.time);
                if(dt){
                    const ms = dt.getTime() - Date.now();
                    if(ms > 0){
                        setTimeout(()=>{
                            try{ audio.play().catch(()=>{}); }catch(e){}
                            alert('Reminder: '+r.task);
                        }, ms);
                    }
                }
            });
        })();
    </script>
@endsection
