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
        Schema::table('book_requests', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('borrowed_at');
            $table->boolean('reminder_sent')->default(false)->after('due_date');
            $table->timestamp('last_reminder_at')->nullable()->after('reminder_sent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_requests', function (Blueprint $table) {
            $table->dropColumn(['due_date', 'reminder_sent', 'last_reminder_at']);
        });
    }
};
