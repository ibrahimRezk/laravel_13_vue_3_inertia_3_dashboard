<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class NationalityFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $data['name']['en'] = fake('english')->name() ;
        $data['name']['ar'] = fake('arabic')->name() ;
        $data['active'] =  random_int(0,1) ;
        $data['added_by'] = 1 ;
        $data['created_at'] = 1 ;
        return $data;
    }

}
