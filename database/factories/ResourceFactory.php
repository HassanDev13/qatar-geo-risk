<?php

namespace Database\Factories;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourceFactory extends Factory
{
    protected $model = Resource::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(),
            'path' => 'resources/' . $this->faker->uuid() . '.pdf',
            'force_ocr' => false,
            'status' => 'done',
            'error_message' => null,
            'rejection_reason' => null,
            'verification_status' => 'unverified',
        ];
    }



}
