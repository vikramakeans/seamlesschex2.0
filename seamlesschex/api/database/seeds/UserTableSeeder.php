<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        DB::table('users')->delete();

        $users = array(
                ['name' => 'Sumanta', 'username' => 'sumantaroot', 'email' => 'sumantaroot@seamlesschex.com', 'password' => Hash::make('Root1Pass@123')],
                ['name' => 'Velu', 'username' => 'veluroot', 'email' => 'veluroot@seamlesschex.com', 'password' => Hash::make('Root2Pass@123')],
                ['name' => 'Albert', 'username' => 'seamlesschexroot', 'email' => 'root@seamlesschex.com', 'password' => Hash::make('Root3Pass@123')],
        );
            
        // Loop through each user above and create the record for them in the database
        foreach ($users as $user)
        {
            User::create($user);
        }

        Model::reguard();
    }
}
