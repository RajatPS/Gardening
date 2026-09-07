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
            'address' => 'Near Madharihat Market, NH31',
            'house_no' => '12A',
            'street' => 'NH31 Service Road',
            'city' => 'Madharihat',
            'state' => 'West Bengal',
            'pincode' => '736135',
            'country' => 'India',
        ]);

        User::updateOrCreate([
            'email' => 'testadmin@local.dev',
        ], [
            'name' => 'Test Admin',
            'password' => bcrypt('11223344'),
            'role' => 'admin',
            'status' => 'active',
            'user_type' => 'admin',
            'address' => 'Branch Office, Near Madharihat Market',
            'house_no' => '1',
            'street' => 'NH31 Main Road',
            'city' => 'Madharihat',
            'state' => 'West Bengal',
            'pincode' => '736135',
            'country' => 'India',
        ]);

        User::where('email', 'admin1@gmail.com')->update(['email' => 'testadmin@local.dev']);

        $madharihat = Branch::updateOrCreate(
            ['name' => 'Madharihat'],
            [
                'name' => 'Madharihat',
                'address' => 'Near Madharihat Market, NH31, West Bengal 736135',
                'latitude' => 26.5013,
                'longitude' => 89.3485,
            ]
        );

        $kolkata = Branch::updateOrCreate(
            ['name' => 'Kolkata'],
            [
                'name' => 'Kolkata',
                'address' => 'Eden Gardens Area, Kolkata, West Bengal 700021',
                'latitude' => 22.5726,
                'longitude' => 88.3639,
            ]
        );

        User::updateOrCreate([
            'email' => 's1@gmail.com',
        ], [
            'name' => 'Staff One',
            'password' => bcrypt('1122334455'),
            'role' => 'staff',
            'status' => 'active',
            'user_type' => 'staff',
            'branch_id' => $madharihat->id,
            'address' => 'Near Madharihat Market, NH31',
            'house_no' => '9',
            'street' => 'NH31 Service Road',
            'city' => 'Madharihat',
            'state' => 'West Bengal',
            'pincode' => '736135',
            'country' => 'India',
        ]);

        User::updateOrCreate([
            'email' => 's2@gmail.com',
        ], [
            'name' => 'Staff Two',
            'password' => bcrypt('1122334455'),
            'role' => 'staff',
            'status' => 'active',
            'user_type' => 'staff',
            'branch_id' => $madharihat->id,
            'address' => 'Opposite Madharihat Bazaar',
            'house_no' => '11',
            'street' => 'Bazaar Road',
            'city' => 'Madharihat',
            'state' => 'West Bengal',
            'pincode' => '736135',
            'country' => 'India',
        ]);

        User::updateOrCreate([
            'email' => 's3@gmail.com',
        ], [
            'name' => 'Staff Three',
            'password' => bcrypt('1122334455'),
            'role' => 'staff',
            'status' => 'active',
            'user_type' => 'staff',
            'branch_id' => $madharihat->id,
            'address' => 'EV Street, Close to Madharihat Station',
            'house_no' => '18',
            'street' => 'Station Road',
            'city' => 'Madharihat',
            'state' => 'West Bengal',
            'pincode' => '736135',
            'country' => 'India',
        ]);

        User::updateOrCreate(
            ['email' => 'ravi.kumar@example.com'],
            [
                'name' => 'Ravi Kumar',
                'password' => bcrypt('1122334455'),
                'role' => 'staff',
                'status' => 'active',
                'user_type' => 'staff',
                'branch_id' => $madharihat->id,
                'staff_id' => 'STF-M001',
                'capabilities' => ['Appointment Service', 'Delivery'],
                'current_duty' => 'Appointment Service',
            ]
        );

        User::updateOrCreate(
            ['email' => 'amit.das@example.com'],
            [
                'name' => 'Amit Das',
                'password' => bcrypt('1122334455'),
                'role' => 'staff',
                'status' => 'active',
                'user_type' => 'staff',
                'branch_id' => $madharihat->id,
                'staff_id' => 'STF-M002',
                'capabilities' => ['Appointment Service', 'Installation'],
                'current_duty' => 'Appointment Service',
            ]
        );

        User::updateOrCreate(
            ['email' => 'suman.roy@example.com'],
            [
                'name' => 'Suman Roy',
                'password' => bcrypt('1122334455'),
                'role' => 'staff',
                'status' => 'active',
                'user_type' => 'staff',
                'branch_id' => $madharihat->id,
                'staff_id' => 'STF-M003',
                'capabilities' => ['Maintenance', 'Inspection'],
                'current_duty' => 'Maintenance',
            ]
        );

        User::updateOrCreate(
            ['email' => 'rahul.sharma@example.com'],
            [
                'name' => 'Rahul Sharma',
                'password' => bcrypt('1122334455'),
                'role' => 'staff',
                'status' => 'active',
                'user_type' => 'staff',
                'branch_id' => $madharihat->id,
                'staff_id' => 'STF-M004',
                'capabilities' => ['Delivery', 'Installation'],
                'current_duty' => 'Delivery',
            ]
        );

        User::updateOrCreate(
            ['email' => 'priya.das@example.com'],
            [
                'name' => 'Priya Das',
                'password' => bcrypt('1122334455'),
                'role' => 'staff',
                'status' => 'active',
                'user_type' => 'staff',
                'branch_id' => $madharihat->id,
                'staff_id' => 'STF-M005',
                'capabilities' => ['Inspection', 'Maintenance'],
                'current_duty' => 'Inspection',
            ]
        );

        User::updateOrCreate(
            ['email' => 'arjun.roy@example.com'],
            [
                'name' => 'Arjun Roy',
                'password' => bcrypt('1122334455'),
                'role' => 'staff',
                'status' => 'active',
                'user_type' => 'staff',
                'branch_id' => $kolkata->id,
                'staff_id' => 'STF-K001',
                'capabilities' => ['Appointment Service', 'Delivery'],
                'current_duty' => 'Appointment Service',
            ]
        );

        User::updateOrCreate(
            ['email' => 'sourav.das@example.com'],
            [
                'name' => 'Sourav Das',
                'password' => bcrypt('1122334455'),
                'role' => 'staff',
                'status' => 'active',
                'user_type' => 'staff',
                'branch_id' => $kolkata->id,
                'staff_id' => 'STF-K002',
                'capabilities' => ['Installation', 'Maintenance'],
                'current_duty' => 'Installation',
            ]
        );

        User::updateOrCreate(
            ['email' => 'ankit.kumar@example.com'],
            [
                'name' => 'Ankit Kumar',
                'password' => bcrypt('1122334455'),
                'role' => 'staff',
                'status' => 'active',
                'user_type' => 'staff',
                'branch_id' => $kolkata->id,
                'staff_id' => 'STF-K003',
                'capabilities' => ['Appointment Service', 'Inspection'],
                'current_duty' => 'Appointment Service',
            ]
        );

        User::updateOrCreate(
            ['email' => 'neha.roy@example.com'],
            [
                'name' => 'Neha Roy',
                'password' => bcrypt('1122334455'),
                'role' => 'staff',
                'status' => 'active',
                'user_type' => 'staff',
                'branch_id' => $kolkata->id,
                'staff_id' => 'STF-K004',
                'capabilities' => ['Delivery', 'Maintenance'],
                'current_duty' => 'Delivery',
            ]
        );

        User::updateOrCreate(
            ['email' => 'puja.das@example.com'],
            [
                'name' => 'Puja Das',
                'password' => bcrypt('1122334455'),
                'role' => 'staff',
                'status' => 'active',
                'user_type' => 'staff',
                'branch_id' => $kolkata->id,
                'staff_id' => 'STF-K005',
                'capabilities' => ['Inspection', 'Appointment Service'],
                'current_duty' => 'Inspection',
            ]
        );

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
