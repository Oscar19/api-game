<?php
namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(), // Relación con el usuario
            'dice1' => $this->faker->numberBetween(1, 6),
            'dice2' => $this->faker->numberBetween(1, 6),
            'winner' => $this->faker->boolean,
        ];
    }
}

