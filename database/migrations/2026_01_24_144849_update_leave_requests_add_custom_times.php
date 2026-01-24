<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateLeaveRequestsAddCustomTimes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->time('start_time')->nullable()->after('end_date');
            $table->time('end_time')->nullable()->after('start_time');
        });

        // Use raw SQL to update the enum to include 'custom' as Laravel 8's enum change is limited without doctrine/dbal
        // This works for MySQL/MariaDB
        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN leave_type ENUM('full_day', 'morning', 'afternoon', 'custom')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time']);
        });
        
        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN leave_type ENUM('full_day', 'morning', 'afternoon')");
    }
}
