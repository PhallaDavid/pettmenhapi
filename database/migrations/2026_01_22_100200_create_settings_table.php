<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default late penalty settings
        \DB::table('settings')->insert([
            ['key' => 'late_10_min_penalty', 'value' => '5', 'description' => 'Percentage deduction for 10-29 minutes late', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'late_30_min_penalty', 'value' => '10', 'description' => 'Percentage deduction for 30+ minutes late', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
