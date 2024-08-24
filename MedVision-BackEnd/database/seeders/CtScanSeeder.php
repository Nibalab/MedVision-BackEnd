<?php

namespace Database\Seeders;

use App\Models\CtScan;
use Illuminate\Database\Seeder;

class CtScanSeeder extends Seeder
{
    public function run()
    {
        CtScan::factory()->count(10)->create();
    }
}
