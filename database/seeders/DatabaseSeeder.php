<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Product;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => bcrypt('1122334455'),
            'role' => 'customer',
            'status' => 'active',
            'user_type' => 'customer',
        ]);

        User::updateOrCreate([
            'email' => 'admin1@gmail.com',
        ], [
            'name' => 'Admin One',
            'password' => bcrypt('1122334455'),
            'role' => 'admin',
            'status' => 'active',
            'user_type' => 'admin',
        ]);

        User::updateOrCreate([
            'email' => 'u1@gmail.com',
        ], [
            'name' => 'U1 User',
            'password' => bcrypt('1122334455'),
            'role' => 'customer',
            'status' => 'active',
            'user_type' => 'customer',
        ]);

        User::updateOrCreate([
            'email' => 's1@gmail.com',
        ], [
            'name' => 'Staff One',
            'password' => bcrypt('1122334455'),
            'role' => 'staff',
            'status' => 'active',
            'user_type' => 'staff',
        ]);

        User::updateOrCreate([
            'email' => 's2@gmail.com',
        ], [
            'name' => 'Staff Two',
            'password' => bcrypt('1122334455'),
            'role' => 'staff',
            'status' => 'active',
            'user_type' => 'staff',
        ]);

        User::updateOrCreate([
            'email' => 's3@gmail.com',
        ], [
            'name' => 'Staff Three',
            'password' => bcrypt('1122334455'),
            'role' => 'staff',
            'status' => 'active',
            'user_type' => 'staff',
        ]);

        Branch::updateOrCreate(['name' => 'Madharihat'], ['name' => 'Madharihat']);
        Branch::updateOrCreate(['name' => 'Kolkata'], ['name' => 'Kolkata']);

        Product::query()->upsert([
            [
                'name' => 'Areca Palm',
                'type' => 'plant',
                'category' => 'air-purifying',
                'sku' => 'PLANT-ARECA-001',
                'price' => 799,
                'stock' => 24,
                'specifications' => json_encode(['height' => '2 ft', 'pot' => 'nursery pot']),
                'care_profile' => json_encode(['sunlight' => 'bright indirect', 'water' => 'twice weekly']),
                'is_pet_safe' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Ceramic Self-Watering Pot',
                'type' => 'accessory',
                'category' => 'decorative-pots',
                'sku' => 'POT-CERAMIC-001',
                'price' => 1199,
                'stock' => 18,
                'specifications' => json_encode(['material' => 'ceramic', 'finish' => 'matte']),
                'care_profile' => null,
                'is_pet_safe' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Organic Growth Kit',
                'type' => 'care-product',
                'category' => 'fertilizers',
                'sku' => 'CARE-GROWTH-001',
                'price' => 649,
                'stock' => 36,
                'specifications' => json_encode(['contents' => 'soil booster, compost, micronutrients']),
                'care_profile' => json_encode(['frequency' => 'monthly']),
                'is_pet_safe' => false,
                'is_active' => true,
            ],
        ], ['sku']);

        SubscriptionPlan::query()->upsert([
            [
                'name' => 'Basic',
                'monthly_price' => 899,
                'visit_cadence' => 'Monthly visit',
                'features' => json_encode(['Health inspection', 'Care checklist', 'Reminder setup']),
                'priority_support' => false,
                'emergency_assistance' => false,
            ],
            [
                'name' => 'Standard',
                'monthly_price' => 1799,
                'visit_cadence' => 'Two visits per month',
                'features' => json_encode(['Fertilizer application', 'Plant care reminders', 'Priority booking']),
                'priority_support' => true,
                'emergency_assistance' => false,
            ],
            [
                'name' => 'Premium',
                'monthly_price' => 3499,
                'visit_cadence' => 'Weekly visit',
                'features' => json_encode(['Emergency assistance', 'Detailed health reports', 'Priority support']),
                'priority_support' => true,
                'emergency_assistance' => true,
            ],
        ], ['name']);
    }
}
