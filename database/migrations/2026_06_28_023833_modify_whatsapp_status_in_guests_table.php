<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ubah tipe kolom menjadi string agar lebih fleksibel dibanding ENUM
        Schema::table('guests', function (Blueprint $table) {
            $table->string('whatsapp_status', 20)->default('pending')->change();
        });

        // 2. Migrasikan data status lama ke status baru
        DB::table('guests')->where('whatsapp_status', 'waiting')->update(['whatsapp_status' => 'pending']);
        // Perhatian: Pada sistem lama, 'sent' berarti sedang memproses
        DB::table('guests')->where('whatsapp_status', 'sent')->update(['whatsapp_status' => 'processing']);
        // Dan 'delivered' berarti sukses dikirim
        DB::table('guests')->where('whatsapp_status', 'delivered')->update(['whatsapp_status' => 'sent']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan data status
        DB::table('guests')->where('whatsapp_status', 'sent')->update(['whatsapp_status' => 'delivered']);
        DB::table('guests')->where('whatsapp_status', 'processing')->update(['whatsapp_status' => 'sent']);
        DB::table('guests')->where('whatsapp_status', 'pending')->update(['whatsapp_status' => 'waiting']);

        Schema::table('guests', function (Blueprint $table) {
            $table->enum('whatsapp_status', ['waiting', 'sent', 'delivered', 'failed'])->default('waiting')->change();
        });
    }
};
