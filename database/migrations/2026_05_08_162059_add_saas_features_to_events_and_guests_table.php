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
        Schema::table('events', function (Blueprint $table) {
            $table->string('attendance_type')->default('checkin_only')->after('is_active');
            $table->string('invitation_template')->default('formal')->after('attendance_type');
            $table->json('template_config')->nullable()->after('invitation_template');
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->string('rsvp_status')->default('pending')->after('qr_code'); // pending, attending, declined, unsure
            $table->integer('plus_one_count')->default(0)->after('rsvp_status');
            $table->timestamp('rsvp_at')->nullable()->after('plus_one_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['attendance_type', 'invitation_template', 'template_config']);
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn(['rsvp_status', 'plus_one_count', 'rsvp_at']);
        });
    }
};
