<?php

namespace Database\Factories;

use App\Models\ResourcePage;
use App\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;

class TermFactory extends Factory
{
    protected $model = Term::class;

    public function definition(): array
    {
        return [
            'resource_page_id' => ResourcePage::factory(),
            'term_en' => $this->faker->word(),
            'term_ar' => $this->faker->word(),
            'confidence_level' => $this->faker->randomFloat(2, 0.5, 1.0),
            'corrections' => null,
            'positive_feedback_count' => $this->faker->numberBetween(0, 10),
            'negative_feedback_count' => $this->faker->numberBetween(0, 5),
            'total_feedback_count' => 0,
            'x' => $this->faker->numberBetween(0, 1000),
            'y' => $this->faker->numberBetween(0, 1000),
            'width' => $this->faker->numberBetween(50, 200),
            'height' => $this->faker->numberBetween(20, 50),
            'status' => 'unverified',
            'rejection_reason' => null,
            'source_url' => null,
            'source_type' => 'gpt',
        ];
    }

}
