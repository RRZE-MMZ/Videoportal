<?php

namespace Database\Factories;

use App\Models\Clip;
use App\Models\Context;
use App\Models\Format;
use App\Models\Image;
use App\Models\Organization;
use App\Models\Semester;
use App\Models\Type;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ClipFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Clip::class;

    /**
     * Define the model's default state
     */
    public function definition(): array
    {
        static $episode = 0;

        return [
            'title' => $title = $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'recording_date' => Carbon::now()->format('d-m-Y H:i:s'),
            'slug' => $title,
            'organization_id' => Organization::factory()->create()->org_id,
            'language_id' => '1', // 1 is de, 2 should be  en
            'context_id' => Context::factory()->create()->id,
            'format_id' => Format::factory()->create()->id,
            'type_id' => Type::factory()->create()->id,
            'owner_id' => User::factory()->create()->id,
            'semester_id' => Semester::current()->first()->id,
            'posterImage' => null,
            'series_id' => null,
            'episode' => $episode++,
            'is_public' => true,
            'is_livestream' => false,
            'image_id' => Image::factory()->create()->id,
            'has_time_availability' => false,
            'time_availability_start' => Carbon::now(),
            'time_availability_end' => Carbon::now(),
        ];
    }
}
