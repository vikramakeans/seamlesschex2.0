<?php

use Illuminate\Database\Seeder;
use App\User;
class CustomSeeds extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		
		$faker = Faker\Factory::create();
 
		for ($i = 0; $i < 500; $i++)
		{
		  $password = Hash::make($faker->word);
		  $user = User::create(array(
			'name' => $faker->name,
			'email' => $faker->unique()->email,
			'password' => $password
		  ));
		}
    }
}
