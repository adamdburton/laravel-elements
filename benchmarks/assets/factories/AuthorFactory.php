<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use \Click\Elements\Benchmarks\Assets\Models\Author;

$factory->define(Author::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'born' => $faker->year,
    ];
});
