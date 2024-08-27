<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            CtScanSeeder::class,
            ThreeDModelSeeder::class,
            AnnotationSeeder::class,
            ReportSeeder::class,
            AppointmentSeeder::class,
            MessageSeeder::class,
            AdminLogSeeder::class,
            DoctorSeeder::class,
        ]);
    }
}

