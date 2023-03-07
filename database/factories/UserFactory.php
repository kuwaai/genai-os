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
			'email' => 'demo' . ($count - 1) . '@chat.cdalab.tw',
			'email_verified_at' => now(),
			'password' => Hash::make("chatchat"),
			'isAdmin' => false,
			'forDemo' => true
		];
	}

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
