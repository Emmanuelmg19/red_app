<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $users = User::factory(5)->create()->each(function ($user) {
            Image::factory()->create([
                'imageable_id' => $user->id,
                'imageable_type' => User::class,
            ]);
        });
    }
}