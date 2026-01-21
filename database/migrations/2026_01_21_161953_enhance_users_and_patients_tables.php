<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnhanceUsersAndPatientsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('preferences')->nullable()->after('address'); // Stores {theme: 'dark', lang: 'en', sidebar: 'open'}
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->string('status')->default('active')->after('name'); // active, inactive, pending
            $table->boolean('is_follow_up')->default(false)->after('status');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('preferences');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['status', 'is_follow_up']);
        });
    }
}
