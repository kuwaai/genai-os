<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
	public function definition(): array
	{
		static $count = 1;
		
		return [
			'name' => 'demo' . $count++,
			'email' => 'demo' . ($count - 1) . '@chat.gai.tw',
			'email_verified_at' => now(),
			'password' => Hash::make("chatchat"),
			'isAdmin' => false,
			'forDemo' => true
		];
	}
}
