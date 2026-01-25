<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequiresEndDateToLeaveCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leave_categories', function (Blueprint $table) {
            $table->boolean('requires_end_date')->default(false)->after('requires_attachment');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_categories', function (Blueprint $table) {
            $table->dropColumn('requires_end_date');
        });
    }
}
