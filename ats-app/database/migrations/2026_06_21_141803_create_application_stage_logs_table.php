<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('application_stage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pipeline_stage_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('entered_at');
            $table->timestamps();
        });

        // Backfill: voor bestaande sollicitaties één logregel in hun huidige fase,
        // zodat de funnel/doorlooptijd ook historische data meeneemt.
        DB::table('applications')
            ->whereNotNull('pipeline_stage_id')
            ->orderBy('id')
            ->each(function ($application) {
                DB::table('application_stage_logs')->insert([
                    'application_id' => $application->id,
                    'pipeline_stage_id' => $application->pipeline_stage_id,
                    'entered_at' => $application->created_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_stage_logs');
    }
};
