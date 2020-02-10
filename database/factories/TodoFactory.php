<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\Todo;
use Faker\Generator as Faker;

$factory->define(Todo::class, function (Faker $faker) {
    return [
        'created_at' => now(),
        'updated_at' => now(),
        'todo' => $faker->realText(40),
        'status' => $faker->randomElement(['active', 'completed']),
    ];
});
