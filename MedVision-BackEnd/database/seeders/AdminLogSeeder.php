<?php

namespace Database\Seeders;

use App\Models\AdminLog;
use Illuminate\Database\Seeder;

class AdminLogSeeder extends Seeder
{
    public function run()
    {
        AdminLog::factory()->count(10)->create();
    }
}

