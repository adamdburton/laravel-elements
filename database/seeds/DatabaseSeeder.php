<?php

namespace Click\Elements\Database\Seeds;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(ElementSeeder::class);
    }
}
