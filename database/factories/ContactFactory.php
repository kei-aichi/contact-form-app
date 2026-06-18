<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::inRandomOrder()->first()->id,
            'first_name' => fake('ja_JP')->firstName(),
            'last_name' => fake('ja_JP')->lastName(),
            'gender' => fake()->randomElement([1, 2, 3]),
            'email' => fake()->safeEmail(),
            'tel' => fake()->numerify('090########'),
            'address' => fake('ja_JP')->address(),
            'building' => fake()->optional()->secondaryAddress(),
            'detail' => fake('ja_JP')->realText(100),
        ];
    }
}
