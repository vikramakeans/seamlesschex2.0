<?php

use Illuminate\Database\Seeder;
use App\UserDetail;
use App\CompanyDetail;
class CustomSeeds3 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {	
		$j = 1;
		for ($i = 5; $i < 504; $i++)
		{
			$j++;
			$userDetail = UserDetail::create(array(
				'user_id' => $i,
				'company_id' => $j,
				'status_id' => 1,
				'role_id' => 3
			  ));
			$comapnyDetail = CompanyDetail::create(array(
				'user_id' => $i,
				'company_id' => $j,
				'total_no_check' => 3,
				'no_of_check_remaining' => 3,
				'total_fundconfirmation' => 0,
				'remaining_fundconfirmation' => 0,
				'total_payauth' => 0,
				'payauth_remaining' => 0,
				'companies_permission' => 'no',
				'payment_link_permission' => 'yes',
				'signture_permission' => 'no',
				'pay_auth_permission' => 'no',
				'status_id' => 1,
			  ));
		}
    }
}
