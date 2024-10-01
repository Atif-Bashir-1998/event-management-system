<?php

namespace Database\Factories;

use App\Constants\Event\Constants;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $event_type = fake()->randomElement(array_values(Constants::EVENT_TYPES));
        $event_status = fake()->randomElement(array_values(Constants::EVENT_STATUSES));
        $start_date = Carbon::now()->addDays(fake()->numberBetween(1, 30));
        $end_date = (clone $start_date)->addDays(fake()->numberBetween(1, 3));

        return [
            'name' => fake()->sentence,
            'event_type' => $event_type,
            'description' => fake()->paragraph,
            'image_url' => fake()->imageUrl(),
            'capacity_limit' => fake()->numberBetween(50, 200),
            'waiting_list_size' => fake()->numberBetween(0, 50),
            'automatic_ticket_upgrade' => fake()->boolean(80),
            'start_date' => $start_date->format('Y-m-d H:i:s'),
            'end_date' => $end_date->format('Y-m-d H:i:s'),
            'location' => fake()->address,
            'status' => $event_status,
            'cancellation_policy' => fake()->text(200),
            'created_by' => User::inRandomOrder()->first()->id,
        ];
    }
}
