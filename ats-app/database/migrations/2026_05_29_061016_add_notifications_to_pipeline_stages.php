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
        Schema::table('pipeline_stages', function (Blueprint $table) {
            $table->boolean('notify_applicant')->default(false)->after('is_default');
            $table->string('email_subject')->nullable()->after('notify_applicant');
            $table->longText('email_body')->nullable()->after('email_subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pipeline_stages', function (Blueprint $table) {
            $table->dropColumn(['notify_applicant', 'email_subject', 'email_body']);
        });
    }
};
