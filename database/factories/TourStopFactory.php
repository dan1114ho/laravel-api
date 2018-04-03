<?php

use Faker\Generator as Faker;
use App\TourStop;

$factory->define(App\TourStop::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence(),
        'description' => $faker->paragraph(),
        'location_type' => TourStop::$LOCATION_TYPES[array_rand(TourStop::$LOCATION_TYPES)],
        'order' => 1,
    ];
});
