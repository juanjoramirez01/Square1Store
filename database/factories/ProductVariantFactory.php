<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::inRandomOrder()->first(),
            'color' => $this->faker->randomElement(['Red', 'Black', 'White', 'Blue']),
            'size' => $this->faker->randomElement(['S', 'M', 'L', 'XL']),
            'stock_quantity' => $this->faker->numberBetween(1, 100),
        ];
    }
}
