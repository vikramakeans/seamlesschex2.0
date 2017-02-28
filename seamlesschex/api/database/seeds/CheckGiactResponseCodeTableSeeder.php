<?php

use Illuminate\Database\Seeder;

class CheckGiactResponseCodeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        DB::table('check_giact_response_codes')->insert(['code' => '_1111', 'details' => 'Pass AV', 'pass' => '1', 'description' => 'Account Verified – The checking account was found to be an open and valid account.', 'type' => 'success', 'created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => '_2222', 'details' => 'Pass AMEX', 'pass' => '1', 'description' => 'AMEX – The account was found to be an open and valid American Express account', 'type' => 'success','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => '_3333', 'details' => 'Pass NPP', 'pass' => '1', 'description' => 'Non-Participant Provider – This account was reported with acceptable, positive data found in recent or current transactions.', 'type' => 'success','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => '_5555', 'details' => 'Pass SAV', 'pass' => '1', 'description' => 'Savings Account Verified – The savings account was found to be an open and valid account.', 'type' => 'success','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => '_7777', 'details' => 'Pass AV', 'pass' => '1', 'description' => 'Account Verified – The checking account was found to be open and have a positive history.', 'type' => 'success','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => '_8888', 'details' => 'Pass SAV', 'pass' => '1', 'description' => 'Savings Account Verified – The savings account was found to be open and have a positive history. ', 'type' => 'success','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => '_9999', 'details' => 'Pass TAWRWA', 'pass' => '1', 'description' => 'This account was reported with acceptable, positive data found in recent transactions. Positive history exists for multiple transactions', 'type' => 'success','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'ND00', 'details' => 'No Data', 'pass' => '1', 'description' => 'No positive or negative information has been reported on the account. This could be a small or regional bank that does not report.', 'type' => 'warning','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'ND01', 'details' => 'No Data - US Government Only', 'pass' => '1', 'description' => 'No positive or negative information has been reported on the account. This routing number can only be valid for US Government financial institutions. Please verify this item with its issuing authority.', 'type' => 'warning','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'GS01', 'details' => 'Invalid Routing Number', 'pass' => '0', 'description' => 'Invalid/Unassigned Routing Number - The routing number supplied either fails the validation test or is not currently assigned to a bank.', 'type' => 'Declined','created_at' => $now,
                'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'GP01', 'details' => 'Variable', 'pass' => '0', 'description' => 'The account was found as active in your Private Bad Checks List.', 'type' => 'PrivateBadChecksList','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'RT00', 'details' => 'No Information Found', 'pass' => '0', 'description' => 'The routing number appears to be accurate however no positive or negative information has been reported on the account. Please contact customer to ensure that the correct account information was entered.', 'type' => 'Declined','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'RT01', 'details' => 'Declined', 'pass' => '0', 'description' => 'This account should be returned based on the risk factor being reported.', 'type' => 'Declined','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'RT02', 'details' => 'Reject Item', 'pass' => '0', 'description' => 'This item should be returned based on the risk factor being reported', 'type' => 'RejectItem','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'RT03', 'details' => 'Accept With Risk', 'pass' => '0', 'description' => 'Current negative data exists on this account. Accept transaction with risk. (Example: Checking or savings accounts in NSF status, recent returns, or outstanding items) ', 'type' => 'AcceptWithRisk','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'RT04', 'details' => 'No Negative Data', 'pass' => '0', 'description' => 'This account is a Non-Demand Deposit Account (post no debits), Credit Card Check, Line of Credit, Home Equity or a Brokerage check.', 'type' => 'PassNdd','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'RT05', 'details' => 'AcceptWithRisk', 'pass' => '0', 'description' => 'Recent negative data exists on this account. Accept transaction with risk. (Example: Checking or savings accounts in NSF status, recent returns, or outstanding items) ', 'type' => 'AcceptWithRisk','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'GN01', 'details' => 'Negative Data', 'pass' => '0', 'description' => "Negative information was found in this account's history.", 'type' => 'NegativeData','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'GN05', 'details' => 'Declined', 'pass' => '0', 'description' => 'The routing number supplied is reported as not assigned to a financial institution.', 'type' => 'Declined','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'CA01', 'details' => 'Declined', 'pass' => '0', 'description' => "Information submitted failed customer authentication.", 'type' => 'Declined','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'CI01', 'details' => 'Declined', 'pass' => '0', 'description' => "Information submitted failed customer identification.", 'type' => 'Declined','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'CI02', 'details' => 'Declined', 'pass' => '0', 'description' => "OFAC Alert - Information submitted was found on the OFAC list.", 'type' => 'Declined','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'CA11', 'details' => 'Pass', 'pass' => '1', 'description' => "Customer authentication passed.", 'type' => 'success','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'CI11', 'details' => 'Pass', 'pass' => '1', 'description' => "Customer identification passed.", 'type' => 'success','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'CA21', 'details' => 'RiskAlert', 'pass' => '0', 'description' => "The customer's individual name or business name data entered did not match authentication data.", 'type' => 'RiskAlert','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'CI21', 'details' => 'RiskAlert', 'pass' => '0', 'description' => "The customer's individual name or business name data entered did not match Identification data.", 'type' => 'RiskAlert','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'CA22', 'details' => 'RiskAlert', 'pass' => '0', 'description' => "The customer's TaxId (SSN/ITIN) data entered did not match authentication data", 'type' => 'RiskAlert','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'CI22', 'details' => 'RiskAlert', 'pass' => '0', 'description' => "The customer's TaxId (SSN/ITIN) data entered did not match identification data.", 'type' => 'RiskAlert','created_at' => $now,'updated_at' => $now]);
        DB::table('check_giact_response_codes')->insert(['code' => 'CA23', 'details' => 'AcceptWithRisk ', 'pass' => '0', 'description' => "The customer's address data entered did not match authentication data.", 'type' => 'AcceptWithRisk','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'CI23', 'details' => 'AcceptWithRisk ', 'pass' => '0', 'description' => "The customer's address data entered did not match identification data.", 'type' => 'AcceptWithRisk','created_at' => $now, 'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'CA24', 'details' => 'AcceptWithRisk ', 'pass' => '0', 'description' => "The customer's phone data entered did not match authentication data.", 'type' => 'AcceptWithRisk','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'CI24', 'details' => 'AcceptWithRisk ', 'pass' => '0', 'description' => "The customer's phone data entered did not match identification data", 'type' => 'AcceptWithRisk','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'CA25', 'details' => 'AcceptWithRisk ', 'pass' => '0', 'description' => "The customer's date of birth or identification data entered did not match authentication data.", 'type' => 'AcceptWithRisk','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'CI25', 'details' => 'AcceptWithRisk ', 'pass' => '0', 'description' => "The customer's date of birth or identification data entered did not match identification data.", 'type' => 'AcceptWithRisk','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'CA30', 'details' => 'RiskAlert', 'pass' => '0','description' => 'Multiple secondary data points did not match authentication data.', 'type' => 'RiskAlert','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'CI30', 'details' => 'RiskAlert', 'pass' => '0', 'description' => "Multiple secondary data points did not match identification data.", 'type' => 'RiskAlert','created_at' => $now,'updated_at' => $now]);

        DB::table('check_giact_response_codes')->insert(['code' => 'ND02', 'details' => 'NoData', 'pass' => '0', 'description' => "No customer data was found for the information submitted.", 'type' => 'NoData','created_at' => $now,'updated_at' => $now]);
    }
}
