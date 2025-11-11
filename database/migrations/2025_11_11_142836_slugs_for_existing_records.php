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
        // Services
        DB::table('services')->get()->each(function ($service) {
            $slug = Str::slug($service->service_name);
            $count = 1;

            // Make sure slug is unique
            while (DB::table('services')->where('slug', $slug)->exists()) {
                $slug = Str::slug($service->service_name) . '-' . $count;
                $count++;
            }

            DB::table('services')->where('id', $service->id)->update(['slug' => $slug]);
        });

        // Works
        DB::table('works')->get()->each(function ($work) {
            $slug = Str::slug($work->project_title);
            $count = 1;

            while (DB::table('works')->where('slug', $slug)->exists()) {
                $slug = Str::slug($work->project_title) . '-' . $count;
                $count++;
            }

            DB::table('works')->where('id', $work->id)->update(['slug' => $slug]);
        });
    }

    public function down(): void
    {
        DB::table('services')->update(['slug' => null]);
        DB::table('works')->update(['slug' => null]);
    }
};
