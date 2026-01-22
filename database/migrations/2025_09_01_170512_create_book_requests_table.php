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
        Schema::create('book_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->foreignId('borrower_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Returned'])->default('Pending');
            $table->text('message')->nullable();
            $table->enum('request_type', ['borrow', 'swap'])->default('borrow');
            $table->date('due_date')->nullable();
            $table->foreignId('swap_book_id')->nullable()->constrained('books')->onDelete('cascade');
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('last_reminder_at')->nullable();
            $table->timestamp('borrowed_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_requests');
    }
};
