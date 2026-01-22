<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiseaseCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('disease_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();

            // Pricing (optional)
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('price_promotion', 12, 2)->nullable();
            $table->decimal('promotion_percent', 6, 2)->nullable();

            // Promotion dates (optional)
            $table->date('date_start_promotion')->nullable();
            $table->date('date_end_promotion')->nullable();

            // Calculated/overridden price after promotion (optional)
            $table->decimal('price_after_promotion', 12, 2)->nullable();

            // Extra note (optional)
            $table->text('promotion_note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('disease_categories');
    }
}

