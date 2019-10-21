<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use \Click\Elements\Benchmarks\Assets\Models\Book;

$factory->define(Book::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'released' => $faker->year,
    ];
});
