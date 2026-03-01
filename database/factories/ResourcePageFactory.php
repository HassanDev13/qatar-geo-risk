<?php

namespace Database\Factories;

use App\Models\Resource;
use App\Models\ResourcePage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourcePageFactory extends Factory
{
    protected $model = ResourcePage::class;

    public function definition(): array
    {
        return [
            'resource_id' => Resource::factory(),
            'page_number' => $this->faker->numberBetween(1, 100),
            'image_path' => 'pages/' . $this->faker->uuid() . '.png',
            'text' => $this->faker->paragraph(),
            'layout_data' => json_encode([]),
            'status' => 'done',
            'error_message' => null,
        ];
    }

}
