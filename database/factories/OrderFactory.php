<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first(),
            'total_amount' => $this->faker->randomFloat(2, 50, 500),
            'order_status' => $this->faker->randomElement(['pending', 'shipped', 'delivered']),
            'payment_method' => $this->faker->randomElement(['credit_card', 'paypal']),
            'shipping_address' => $this->faker->address,
        ];
    }
}
