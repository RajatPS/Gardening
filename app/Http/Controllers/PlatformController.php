<?php

namespace App\Http\Controllers;

use App\Services\AppointmentLocalityService;
use Illuminate\Contracts\View\View;

class PlatformController extends Controller
{
    public function home(): View
    {
        return view('pages.home', $this->sharedData());
    }

    public function store(): View
    {
        $data = $this->sharedData();
        $category = request()->query('category');
        if ($category) {
            $data['products'] = array_filter($data['products'], function($p) use ($category) {
                return stripos($p['category'], $category) !== false || stripos($p['name'], $category) !== false;
            });
        }

        return view('pages.store', $data);
    }

    public function services(): View
    {
        return view('pages.services', $this->sharedData());
    }

    public function subscriptions(): View
    {
        return view('pages.subscriptions', $this->sharedData());
    }

    public function aiTools(): View
    {
        return view('pages.ai-tools', $this->sharedData());
    }

    public function reminders(): View
    {
        $data = $this->sharedData();
        $query = \App\Models\Reminder::query();
        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('notified', false);
        }
        $reminders = $query->orderBy('remind_at','asc')->limit(50)->get()->map(function($r){
            return [
                'task' => $r->task,
                'time' => $r->remind_at->format('M d, Y H:i'),
                'type' => $r->type,
            ];
        });

        $data['reminders'] = $reminders;
        return view('pages.reminders', $data);
    }

    public function dashboard(): View
    {
        return view('pages.dashboard', $this->sharedData());
    }

    public function operations(): View
    {
        return view('pages.operations', $this->sharedData());
    }

    public function bookService(
        \Illuminate\Http\Request $request
    ) {
        $validated = $request->validate([
            'service_type' => 'required|string',
            'preferred_at' => 'nullable|date',
            'area_size' => 'nullable|string',
            'address' => 'nullable|string',
            'budget' => 'nullable|numeric',
            'phone' => 'nullable|string',
            'notes' => 'nullable|string',
            'images.*' => 'nullable|image|max:5120'
        ]);

        $localityService = new AppointmentLocalityService();
        $city = $localityService->resolveBookingCity(null, $validated['address'] ?? null);

        $data = [
            'service_type' => $validated['service_type'],
            'preferred_at' => $validated['preferred_at'] ?? null,
            'address_line' => $validated['address'] ?? null,
            'city' => $city,
            'customer_notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ];

        // Handle image uploads
        $uploaded = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('service_images', 'public');
                $uploaded[] = $path;
            }
            $data['uploaded_images'] = $uploaded;
        }

        // Attach authenticated user when available
        if (auth()->check()) {
            $data['user_id'] = auth()->id();
        }

        $booking = \App\Models\ServiceBooking::create($data);

        return redirect()->route('services')->with('success', 'Service request submitted — we will contact you shortly.');
    }

    private function sharedData(): array
    {
        return [
            'categories' => [
                ['name' => 'Indoor Plants', 'count' => '128 items', 'tone' => 'emerald'],
                ['name' => 'Outdoor Plants', 'count' => '96 items', 'tone' => 'teal'],
                ['name' => 'Flowering Plants', 'count' => '74 items', 'tone' => 'rose'],
                ['name' => 'Medicinal Plants', 'count' => '52 items', 'tone' => 'amber'],
                ['name' => 'Air Purifying', 'count' => '41 items', 'tone' => 'cyan'],
                ['name' => 'Bonsai Plants', 'count' => '28 items', 'tone' => 'slate'],
            ],
            'products' => [
                [
                    'name' => 'Areca Palm',
                    'category' => 'Air purifying indoor plant',
                    'price' => '₹799',
                    'care' => 'Bright indirect light',
                    'image' => 'https://images.unsplash.com/photo-1521334884684-d80222895322?auto=format&fit=crop&w=900&q=80',
                ],
                [
                    'name' => 'Ceramic Self-Watering Pot',
                    'category' => 'Decorative pot',
                    'price' => '₹1,199',
                    'care' => 'Matte stone finish',
                    'image' => 'https://images.unsplash.com/photo-1485955900006-10f4d324d411?auto=format&fit=crop&w=900&q=80',
                ],
                [
                    'name' => 'Organic Growth Kit',
                    'category' => 'Fertilizer and soil care',
                    'price' => '₹649',
                    'care' => 'Monthly plant nutrition',
                    'image' => 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?auto=format&fit=crop&w=900&q=80',
                ],
            ],
            'services' => [
                ['name' => 'Garden Setup', 'summary' => 'Design, soil preparation, plant selection, and installation.', 'slot' => '2-4 days'],
                ['name' => 'Plant Maintenance', 'summary' => 'Scheduled pruning, watering audit, nutrition, and cleaning.', 'slot' => 'Monthly'],
                ['name' => 'Plant Health Inspection', 'summary' => 'Disease checks, pest review, care report, and prescriptions.', 'slot' => 'Same week'],
                ['name' => 'Emergency Plant Care', 'summary' => 'Priority visit for disease, pest attack, or irrigation failure.', 'slot' => '24 hours'],
            ],
            'plans' => [
                ['name' => 'Basic', 'price' => '₹100', 'cadence' => 'Monthly visit', 'features' => ['Health inspection', 'Care checklist', 'Reminder setup']],
                ['name' => 'Standard', 'price' => '₹300', 'cadence' => 'Two visits per month', 'features' => ['Fertilizer application', 'Plant care reminders', 'Priority booking']],
                ['name' => 'Premium', 'price' => '₹500', 'cadence' => 'Weekly visit', 'features' => ['Emergency assistance', 'Detailed health reports', 'Priority support']],
            ],
            'aiTools' => [
                ['name' => 'AI Plant Assistant', 'detail' => 'Ask plant, product, service, and website questions by text or voice.'],
                ['name' => 'Disease Detection', 'detail' => 'Upload plant photos and receive diagnosis, causes, treatments, and product suggestions.'],
                ['name' => 'Plant Encyclopedia', 'detail' => 'Search plant benefits, risks, watering, sunlight, soil, growth, and pet safety.'],
                ['name' => 'AI Garden Planner', 'detail' => 'Upload balcony, room, or garden photos for layout and budget suggestions.'],
            ],
            'reminders' => [
                ['task' => 'Water Monstera', 'time' => 'Today, 7:30 PM', 'type' => 'Watering'],
                ['task' => 'Apply fertilizer', 'time' => 'Friday, 9:00 AM', 'type' => 'Nutrition'],
                ['task' => 'Service visit', 'time' => 'Jun 30, 11:00 AM', 'type' => 'Appointment'],
            ],
            'dashboardItems' => [
                'Orders', 'Subscriptions', 'Appointments', 'Saved Products', 'Plant Health Reports', 'Reminder Calendar', 'AI History', 'Payments', 'Addresses',
            ],
        ];
    }
}
