<?php

namespace Database\Seeders;

use App\Models\ThreeDModel;
use Illuminate\Database\Seeder;

class ThreeDModelSeeder extends Seeder
{
    public function run()
    {
        ThreeDModel::factory()->count(10)->create();
    }
}

