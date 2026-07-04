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
        // 1. Create the pivot table
        Schema::create('event_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            
            // A user cannot be assigned to the same event twice
            $table->unique(['event_id', 'user_id']);
        });

        // 2. Migrate existing data from users table
        $users = DB::table('users')->whereNotNull('event_id')->get();
        $inserts = [];
        foreach ($users as $user) {
            $inserts[] = [
                'event_id' => $user->event_id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($inserts)) {
            DB::table('event_user')->insert($inserts);
        }

        // 3. Drop event_id from users table
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key first (might be named users_event_id_foreign depending on Laravel default)
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Add event_id back to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
        });

        // 2. Restore data (best effort: take the first event for each user)
        $eventUsers = DB::table('event_user')->get();
        foreach ($eventUsers as $pivot) {
            DB::table('users')
                ->where('id', $pivot->user_id)
                // Only update if it's null (to keep the first one we find)
                ->whereNull('event_id')
                ->update(['event_id' => $pivot->event_id]);
        }

        // 3. Drop pivot table
        Schema::dropIfExists('event_user');
    }
};
