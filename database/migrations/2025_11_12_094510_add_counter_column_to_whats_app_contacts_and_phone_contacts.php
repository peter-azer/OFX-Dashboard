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
        Schema::table('whats_app_contacts', function (Blueprint $table) {
            $table->string('counter')->nullable();
        });
        Schema::table('phone_contacts', function (Blueprint $table) {
            $table->string('counter')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whats_app_contacts', function (Blueprint $table) {
            $table->dropColumn('counter');
        });

        Schema::table('phone_contacts', function (Blueprint $table) {
            $table->dropColumn('counter');
        });
    }
};
