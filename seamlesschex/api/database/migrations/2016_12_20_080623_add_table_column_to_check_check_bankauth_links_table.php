<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableColumnToCheckCheckBankauthLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('check_bank_auth_links', function (Blueprint $table) {
			$table->text('thank_you_url')->after('memo')->nullable();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('check_bank_auth_links', function (Blueprint $table) {
            $table->dropColumn('thank_you_url');
        });
    }
}
