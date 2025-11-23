<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $provinces = [
            'Maputo', 'Gaza', 'Inhambane', 'Sofala', 'Manica',
            'Tete', 'Zambézia', 'Nampula', 'Cabo Delgado', 'Niassa'
        ];

        $districts = [
            'KaMpfumu', 'Nlhamankulu', 'KaMaxaquene', 'KaMavota', 'KaMubukwana',
            'KaTembe', 'Beira', 'Nampula', 'Quelimane', 'Xai-Xai'
        ];

        // Generate a more unique username by combining faker username with a random string
        // This ensures uniqueness even for large batches
        $username = fake()->userName() . '_' . Str::random(6);

        return [
            'name' => fake()->name(),
            'username' => $username,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'phone' => fake()->optional(0.8)->phoneNumber(),
            'province' => fake()->optional(0.7)->randomElement($provinces),
            'district' => fake()->optional(0.6)->randomElement($districts),
            'neighborhood' => fake()->optional(0.5)->word(),
            'street' => fake()->optional(0.3)->streetAddress(),
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
