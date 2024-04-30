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
        Schema::create('message_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->string('region_name')->nullable()->default(null);
            $table->string('district_name')->nullable()->default(null);
            $table->string('category_name')->nullable()->default(null);
            $table->boolean('status')->default(false);
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->string('timezone')->nullable();
            $table->string('frequency')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_operations');
    }
};
