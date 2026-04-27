<?php

namespace Database\Factories;

use App\Models\CalendarEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    protected $model = CalendarEvent::class;

    public function definition(): array
    {
        $start = $this->faker->optional()->time('H:i');
        $end   = $start ? date('H:i', strtotime($start) + 3600) : null;

        return [
            'title'       => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'event_date'  => $this->faker->dateTimeBetween('now', '+60 days')->format('Y-m-d'),
            'start_time'  => $start,
            'end_time'    => $end,
            'event_type'  => $this->faker->randomElement(array_keys(CalendarEvent::$types)),
            'location'    => $this->faker->optional()->city(),
            'created_by'  => User::factory(),
        ];
    }
}
