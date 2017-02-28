<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Company;

class CustomSeeds2 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
		$faker = Faker\Factory::create();
		
		for ($i = 5; $i < 504; $i++)
		{
		  $user = User::find($i);
		  $user = Company::create(array(
			'user_id' => $i,
			'company_name' => $faker->name,
			'cname' => $faker->name,
			'business_type' => 'Web Development',
			'company_email' => $user->email,
			'phone' => $faker->phoneNumber,
			'address' => $faker->streetAddress,
			'city' => $faker->city,
			'state' => $faker->state,
			'zip' => $faker->postcode,
			'settings' => 'a:1:{s:26:"same_day_processing_cutoff";a:14:{s:8:"mon.time";s:8:"03:00 PM";s:12:"mon.timezone";s:3:"EST";s:8:"tue.time";s:8:"03:00 PM";s:12:"tue.timezone";s:3:"EST";s:8:"wed.time";s:8:"03:00 PM";s:12:"wed.timezone";s:3:"EST";s:8:"thu.time";s:8:"03:00 PM";',
		  ));
		}
    }
}
