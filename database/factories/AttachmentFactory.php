<?php

namespace Database\Factories;

use App\Models\Grievance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mimeTypes = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'text/plain' => 'txt',
        ];

        $mimeType = $this->faker->randomElement(array_keys($mimeTypes));
        $extension = $mimeTypes[$mimeType];
        $filename = $this->faker->slug(3) . '.' . $extension;
        $originalFilename = $this->faker->sentence(3) . '.' . $extension;

        // Generate a fake file hash
        $fileHash = hash('sha256', $this->faker->randomBytes(1024));

        return [
            'grievance_id' => Grievance::factory(),
            'original_filename' => $originalFilename,
            'filename' => $filename,
            'path' => 'attachments/' . $filename, // Fake path for testing
            'mime_type' => $mimeType,
            'size' => $this->faker->numberBetween(1024, 5242880), // 1KB to 5MB
            'file_hash' => $fileHash,
            'is_encrypted' => $this->faker->boolean(20), // 20% encrypted
            'uploaded_by' => User::factory(),
            'uploaded_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Create an image attachment.
     */
    public function image(): static
    {
        return $this->state(function (array $attributes) {
            $mimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $mimeType = $this->faker->randomElement($mimeTypes);
            $extension = match ($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
            };
            $filename = $this->faker->slug(3) . '.' . $extension;
            $originalFilename = $this->faker->sentence(2) . '.' . $extension;

            return [
                'original_filename' => $originalFilename,
                'filename' => $filename,
                'path' => 'attachments/' . $filename,
                'mime_type' => $mimeType,
                'size' => $this->faker->numberBetween(51200, 2097152), // 50KB to 2MB
            ];
        });
    }

    /**
     * Create a PDF attachment.
     */
    public function pdf(): static
    {
        return $this->state(function (array $attributes) {
            $filename = $this->faker->slug(3) . '.pdf';
            $originalFilename = $this->faker->sentence(2) . '.pdf';

            return [
                'original_filename' => $originalFilename,
                'filename' => $filename,
                'path' => 'attachments/' . $filename,
                'mime_type' => 'application/pdf',
                'size' => $this->faker->numberBetween(102400, 10485760), // 100KB to 10MB
            ];
        });
    }

    /**
     * Create a document attachment.
     */
    public function document(): static
    {
        return $this->state(function (array $attributes) {
            $mimeTypes = [
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'text/plain' => 'txt',
            ];
            $mimeType = $this->faker->randomElement(array_keys($mimeTypes));
            $extension = $mimeTypes[$mimeType];
            $filename = $this->faker->slug(3) . '.' . $extension;
            $originalFilename = $this->faker->sentence(2) . '.' . $extension;

            return [
                'original_filename' => $originalFilename,
                'filename' => $filename,
                'path' => 'attachments/' . $filename,
                'mime_type' => $mimeType,
                'size' => $this->faker->numberBetween(5120, 5242880), // 5KB to 5MB
            ];
        });
    }

    /**
     * Create an encrypted attachment.
     */
    public function encrypted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_encrypted' => true,
        ]);
    }

    /**
     * Create an attachment for a specific grievance.
     */
    public function forGrievance(Grievance $grievance): static
    {
        return $this->state(fn (array $attributes) => [
            'grievance_id' => $grievance->id,
        ]);
    }

    /**
     * Create an attachment uploaded by a specific user.
     */
    public function uploadedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'uploaded_by' => $user->id,
        ]);
    }
}
