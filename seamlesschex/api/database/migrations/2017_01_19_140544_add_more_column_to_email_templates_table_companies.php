<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreColumnToEmailTemplatesTableCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_templates', function (Blueprint $table) {
			$table->dropColumn('from');
			$table->text('bcc_email')->after('template_name')->nullable();
			$table->text('cc_email')->after('template_name')->nullable();
			$table->text('from_email')->after('template_name')->nullable();
			$table->text('from_name')->after('template_name')->nullable();		
        });
		Schema::table('companies', function (Blueprint $table) {
            $table->string('mc_token')->unique()->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		 Schema::table('email_templates', function (Blueprint $table) {
			$table->text('from')->after('template_name')->nullable();
			$table->dropColumn('from_name');
			$table->dropColumn('from_email');
			$table->dropColumn('cc_email');
			$table->dropColumn('bcc_email');
		});
		Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('mc_token');
        });
    }
}
