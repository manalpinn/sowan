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
        Schema::table('checkins', function (Blueprint $table) {
            $table->string('method')->default('qr')->change();
            $table->string('checkout_method')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkins', function (Blueprint $table) {
            // Note: Reverting back to ENUM might truncate data if it doesn't match the original enum.
            $table->enum('method', ['qr', 'manual', 'offline'])->default('qr')->change();
            $table->enum('checkout_method', ['qr', 'manual', 'offline'])->nullable()->change();
        });
    }
};
