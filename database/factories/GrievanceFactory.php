<?php

namespace Database\Factories;

use App\Models\Grievance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grievance>
 */
class GrievanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Serviços Públicos',
            'Infraestrutura',
            'Saúde',
            'Educação',
            'Segurança',
            'Transportes',
            'Ambiente',
            'Administração',
        ];

        $provinces = [
            'Maputo',
            'Gaza',
            'Inhambane',
            'Sofala',
            'Manica',
            'Tete',
            'Zambézia',
            'Nampula',
            'Cabo Delgado',
            'Niassa',
        ];

        $districts = [
            'KaMpfumu',
            'Nlhamankulu',
            'KaMaxaquene',
            'KaMavota',
            'KaMubukwana',
            'KaTembe',
        ];

        return [
            'reference_number' => Grievance::generateReferenceNumber(),
            'description' => $this->faker->paragraphs(3, true),
            'category' => $this->faker->randomElement($categories),
            'subcategory' => $this->faker->optional(0.7)->word(),
            'province' => $this->faker->randomElement($provinces),
            'district' => $this->faker->randomElement($districts),
            'location_details' => $this->faker->optional(0.5)->address(),
            'status' => $this->faker->randomElement([
                'submitted',
                'under_review',
                'in_progress',
                'resolved',
                'closed',
                'rejected'
            ]),
            'priority' => $this->faker->randomElement([
                'low',
                'medium',
                'high',
                'urgent'
            ]),
            'is_anonymous' => $this->faker->boolean(30), // 30% anonymous
            'submitted_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Create an identified grievance (with user).
     */
    public function identified(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory(),
            'is_anonymous' => false,
        ]);
    }

    /**
     * Create an anonymous grievance.
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'is_anonymous' => true,
            'contact_name' => $this->faker->name(),
            'contact_email' => $this->faker->optional(0.8)->safeEmail(),
            'contact_phone' => $this->faker->optional(0.6)->phoneNumber(),
        ]);
    }

    /**
     * Create a grievance with specific status.
     */
    public function withStatus(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    /**
     * Create a grievance with specific priority.
     */
    public function withPriority(string $priority): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $priority,
        ]);
    }

    /**
     * Create a resolved grievance.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'resolved_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'resolution_notes' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Create an assigned grievance.
     */
    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => User::factory(),
            'assigned_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'status' => 'in_progress',
        ]);
    }
}
