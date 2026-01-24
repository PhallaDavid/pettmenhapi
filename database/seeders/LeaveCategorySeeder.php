<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LeaveCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'name' => 'Annual Leave',
                'color' => '#4CAF50',
                'icon' => 'calendar-check',
                'requires_attachment' => false,
            ],
            [
                'name' => 'Sick Leave',
                'color' => '#F44336',
                'icon' => 'medical-bag',
                'requires_attachment' => true,
            ],
            [
                'name' => 'Urgent Leave',
                'color' => '#FF9800',
                'icon' => 'clock-alert',
                'requires_attachment' => false,
            ],
            [
                'name' => 'Maternity/Paternity Leave',
                'color' => '#9C27B0',
                'icon' => 'baby-face',
                'requires_attachment' => true,
            ],
            [
                'name' => 'Other',
                'color' => '#9E9E9E',
                'icon' => 'dots-horizontal',
                'requires_attachment' => false,
            ],
        ];

        foreach ($categories as $category) {
            \App\Models\LeaveCategory::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}
