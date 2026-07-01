@extends('layouts.app')

@section('title', 'Services')

@section('content')
    <section class="bg-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-section-heading eyebrow="Service Booking" title="Book expert gardening services" description="Service workflows for setup, maintenance, health inspection, pest control, soil replacement, and emergency plant care." />

            <div class="mt-10">
                <form id="serviceForm" action="{{ route('services.book') }}" method="POST" enctype="multipart/form-data" class="rounded-lg border border-slate-200 bg-stone-50 p-6">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Select Service</label>
                            <select id="serviceType" name="service_type" class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
                                <option value="">Choose a service</option>
                                @foreach($services as $service)
                                    <option value="{{ $service['name'] }}">{{ $service['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Preferred date & time</label>
                            <input type="datetime-local" name="preferred_at" class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div id="dynamicFields" class="mt-6 grid gap-4"></div>

                    <div class="mt-6 flex items-center gap-3">
                        <button type="submit" class="rounded-md bg-emerald-900 px-4 py-2 text-sm font-semibold text-white">Book Service</button>
                        <p class="text-sm text-slate-500">Our experts will contact you to confirm details.</p>
                    </div>
                </form>
            </div>

            <script>
                (function(){
                    const serviceType = document.getElementById('serviceType');
                    const dynamic = document.getElementById('dynamicFields');

                    function clearDynamic(){ dynamic.innerHTML = ''; }

                    function addField(label, inputHtml){
                        const wrapper = document.createElement('div');
                        wrapper.innerHTML = `<label class="block text-sm font-medium text-slate-700">${label}</label>${inputHtml}`;
                        dynamic.appendChild(wrapper);
                    }

                    serviceType.addEventListener('change', function(){
                        clearDynamic();
                        const val = this.value;
                        if(!val) return;

                        // Common fields
                        addField('Area size (sq ft)', '<input name="area_size" class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">');
                        addField('Address', '<input name="address" class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">');

                        if(val === 'Plant Maintenance' || val === 'Plant Health Inspection' || val === 'Emergency Plant Care'){
                            addField('Upload images (multiple)', '<input name="images[]" type="file" multiple accept="image/*" class="mt-1 block w-full text-sm">');
                            addField('Notes', '<textarea name="notes" class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm"></textarea>');
                        }

                        if(val === 'Garden Setup'){
                            addField('Project budget', '<input name="budget" type="number" class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">');
                            addField('Contact phone', '<input name="phone" type="tel" class="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">');
                        }
                    });
                })();
            </script>
        </div>
    </section>
@endsection
