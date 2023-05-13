<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Models\User;

class DemoSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
		$user = User::where('email', 'dev@chat.gai.tw')->first();
		if ($user === null) {
			$user = new User();
			$user->fill([
				'name' => 'dev',
				'email' => 'dev@chat.gai.tw',
				'email_verified_at' => now(),
				'password' => Hash::make("develope"),
				'isAdmin' => true,
				'forDemo' => false
			]);
			$user->save();
			\App\Models\User::factory()->create();
		}
    }
}