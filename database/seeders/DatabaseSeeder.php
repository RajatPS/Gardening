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
        Branch::updateOrCreate(
            ['name' => 'Madharihat Branch'],
            [
                'address' => 'Madharihat, West Bengal, India',
                'latitude' => 26.5013,
                'longitude' => 89.3485,
            ]
        );

        $kolkataBranch = Branch::where('name', 'Eden Gardens Branch')->first();
        if ($kolkataBranch === null) {
            $kolkataBranch = Branch::firstOrNew(['name' => 'Kolkata Branch']);
        }

        $kolkataBranch->fill([
            'name' => 'Kolkata Branch',
            'address' => 'Eden Gardens, Kolkata, West Bengal, India',
            'latitude' => 22.5726,
            'longitude' => 88.3639,
        ])->save();

        // User::updateOrCreate([
        //     'email' => 's1@gmail.com',
        // ], [
        //     'name' => 'Staff One',
        //     'password' => bcrypt('1122334455'),
        //     'role' => 'staff',
        //     'status' => 'active',
        //     'user_type' => 'staff',
        //     'branch_id' => $madharihat->id,
        //     'address' => 'Near Madharihat Market, NH31',
        //     'house_no' => '9',
        //     'street' => 'NH31 Service Road',
        //     'city' => 'Madharihat',
        //     'state' => 'West Bengal',
        //     'pincode' => '736135',
        //     'country' => 'India',
        // ]);

        User::updateOrCreate([
            'email' => 'admin1@gmail.com',
        ], [
            'name' => 'admin One',
            'password' => bcrypt('1122334455'),
            'role' => 'admin',
            'status' => 'active',
            'user_type' => 'admin',
            'country' => 'India',
        ]);


    }
}
