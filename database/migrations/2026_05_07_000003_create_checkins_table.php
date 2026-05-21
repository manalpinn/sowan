<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->timestamp('checkin_time')->nullable();
            $table->timestamp('checkout_time')->nullable();
            $table->enum('status', ['checkin', 'checkout'])->default('checkin');
            $table->enum('method', ['qr', 'manual'])->default('qr');
            $table->timestamps();

            // Prevent double check-in per guest per event
            $table->unique(['event_id', 'guest_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkins');
    }
};
