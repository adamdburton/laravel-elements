<?php

namespace Click\Elements\Database\Seeds;

use Click\Elements\Models\Entity;
use Illuminate\Database\Seeder;

class ElementSeeder extends Seeder
{
    public function run()
    {
        factory(Entity::class)->create();
    }
}
