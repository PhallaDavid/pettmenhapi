<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdatePatientsTableForNewFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            // Drop unique index on email if it exists
            $table->dropUnique(['email']);
            
            // Make old columns nullable using raw SQL safely
            DB::statement('ALTER TABLE patients MODIFY name VARCHAR(255) NULL');
            DB::statement('ALTER TABLE patients MODIFY phone VARCHAR(255) NULL');
            DB::statement('ALTER TABLE patients MODIFY email VARCHAR(191) NULL');

            // Add new fields
            $table->string('fullname')->nullable()->after('id');
            $table->integer('age')->nullable()->after('fullname');
            $table->string('phone_number')->nullable()->after('phone');
            $table->boolean('is_old_patient')->default(false)->after('gender');
            $table->foreignId('disease_category_id')->nullable()->constrained('disease_categories')->onDelete('set null')->after('is_old_patient');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('set null')->after('disease_category_id');
            $table->date('date_come_again')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['disease_category_id']);
            $table->dropForeign(['employee_id']);
            $table->dropColumn(['fullname', 'phone_number', 'age', 'is_old_patient', 'disease_category_id', 'employee_id', 'date_come_again']);
            
            // Restore unique index if possible, but keep nullable to avoid errors
            $table->unique('email');
        });
    }
}
