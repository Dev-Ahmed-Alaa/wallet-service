<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wallet>
 */
class WalletFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = Wallet::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'user_id' => User::factory(),
      'balance' => $this->faker->numberBetween(0, 100000), // 0 to 1000.00 in cents
      'status' => 'active',
    ];
  }

  /**
   * Set the wallet status to inactive.
   *
   * @return \Illuminate\Database\Eloquent\Factories\Factory
   */
  public function inactive(): Factory
  {
    return $this->state(function (array $attributes) {
      return [
        'status' => 'inactive',
      ];
    });
  }
}
