<?php

use Illuminate\Database\Seeder;

class CheckBasicFeeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('check_basic_fees')->insert(['fees_name' => 'MONTHLY_FEE', 'value' => 0.00]);
        DB::table('check_basic_fees')->insert(['fees_name' => 'AGENT_COMMISSION', 'value' => 50]);
        DB::table('check_basic_fees')->insert(['fees_name' => 'AGENT_BUY_DAILY_DEPOSIT_FEE', 'value' => 0.00]);
        DB::table('check_basic_fees')->insert(['fees_name' => 'AGENT_BUY_PER_CHECK_FEE', 'value' => 0.50]);
        DB::table('check_basic_fees')->insert(['fees_name' => 'AGENT_BUY_CHECK_PROCESSING_FEE', 'value' => 1.50]);
        DB::table('check_basic_fees')->insert(['fees_name' => 'AGENT_BUY_CHECK_VERIFICATION_FEE', 'value' => 0.40]);
        DB::table('check_basic_fees')->insert(['fees_name' => 'AGENT_SELL_DAILY_DEPOSIT_FEE', 'value' => 0.00]);
        DB::table('check_basic_fees')->insert(['fees_name' => 'AGENT_SELL_PER_CHECK_FEE', 'value' => 0.50]);
        DB::table('check_basic_fees')->insert(['fees_name' => 'AGENT_SELL_CHECK_PROCESSING_FEE', 'value' => 3.50]);
        DB::table('check_basic_fees')->insert(['fees_name' => 'AGENT_SELL_CHECK_VERIFICATION_FEE', 'value' => 0.40]);
        DB::table('check_basic_fees')->insert(['fees_name' => 'DAILY_DEPOSIT_FEE', 'value' => 0.00]);
        DB::table('check_basic_fees')->insert(['fees_name' => 'PER_CHECK_FEE', 'value' => 0.50]);
        DB::table('check_basic_fees')->insert(['fees_name' => 'CHECK_PROCESSING_FEE', 'value' => 3.50]);
        DB::table('check_basic_fees')->insert(['fees_name' => 'CHECK_VERIFICATION_FEE', 'value' => 0.40]);
        DB::table('check_basic_fees')->insert(['fees_name' => 'FUNDCONFIRMATION_FEE', 'value' => 4.00]);
    }
}
