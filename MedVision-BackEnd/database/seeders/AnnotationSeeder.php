<?php

namespace Database\Seeders;

use App\Models\Annotation;
use Illuminate\Database\Seeder;

class AnnotationSeeder extends Seeder
{
    public function run()
    {
        Annotation::factory()->count(10)->create();
    }
}
