<?php

use Faker\Generator as Faker;
use Illuminate\Notifications\DatabaseNotification;
use Ramsey\Uuid\Uuid;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\User::class, function (Faker $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
        'confirmed' => true,
        'is_admin' => false,
    ];
});


$factory->state(App\User::class, 'admin', function (Faker $faker) {
    return [
        'is_admin' => true,
    ];
});

$factory->state(App\User::class, 'unconfirmed', function (Faker $faker) {
    return [
        'confirmed' => false,
    ];
});

$factory->define(App\Thread::class, function (Faker $faker) {
    $title = $faker->sentence;
    return [
        'title' => title_case($title),
        'slug' => str_slug($title),
        'body' => $faker->paragraph,
        'visits' => 0,
        'locked' => false,
        'user_id' => function () {
            return factory(App\User::class)->create()->id;
        },
        'channel_id' => function () {
            return factory(App\Channel::class)->create()->id;
        }
    ];
});

$factory->define(App\Reply::class, function (Faker $faker) {
    return [
        'body' => $faker->paragraph,
        'thread_id' => function () {
            return factory(App\Thread::class)->create()->id;
        },
        'user_id' => function () {
            return factory(App\User::class)->create()->id;
        }
    ];
});


$factory->define(App\Channel::class, function (Faker $fake) {
    $name = $fake->word;
    return [
        'name' => $name,
        'slug' => $name,
    ];
});

$factory->define(DatabaseNotification::class, function (Faker $fake) {
    return [
        'id' => Uuid::uuid4()->toString(),
        'type' => 'App\Notifications\ThreadWasUpdated',
        'notifiable_id' => auth()->id() ?: create('App\User')->id,
        'notifiable_type' => 'App\User',
        'data' => ['Foo' => "Bar"],
    ];
});
