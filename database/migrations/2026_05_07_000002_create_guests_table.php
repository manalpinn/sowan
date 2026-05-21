<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('qr_code', 64)->unique();
            $table->enum('type', ['VIP', 'Regular', 'VVIP', 'Vendor', 'Media'])->default('Regular');
            $table->string('table_number', 20)->nullable();
            $table->enum('whatsapp_status', ['waiting', 'sent', 'delivered', 'failed'])->default('waiting');
            $table->string('invitation_link')->nullable();
            $table->timestamps();

            // Performance indexes
            $table->index('event_id');
            $table->index('qr_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
