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
        Schema::create('email_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_id')->constrained('emails')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->timestamps();
            
            // Ensure each email can only have one entry per service
            $table->unique(['email_id', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_service');
    }
};
