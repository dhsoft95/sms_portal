<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_code')->nullable();
            $table->string('name');
            $table->unsignedBigInteger('template_id'); // Add template_id column
            $table->unsignedBigInteger('region_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('district_id');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            // Define foreign key constraint
//            $table->foreign('template_id')->references('id')->on('sms_templates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
