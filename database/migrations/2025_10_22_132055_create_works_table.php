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
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            //project title
            $table->string('project_title');
            //project description
            $table->text('project_description');
            //category
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            //arabic
            $table->string('project_title_ar');
            $table->text('project_description_ar');
            //project image
            $table->string('project_image');
            //project link
            $table->string('project_link')->nullable();
            //is active
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('works');
    }
};
