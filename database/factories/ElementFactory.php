<?php

use Click\Elements\Models\Entity;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator as Faker;

/** @var Factory $factory */

$factory->define(Entity::class, function (Faker $faker) {
    return [
//        'uuid' => $faker->uuid
    ];
});
