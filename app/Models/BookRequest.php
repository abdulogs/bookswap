<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'borrower_id',
        'owner_id',
        'status',
        'request_type',
        'swap_book_id',
        'message',
        'borrowed_at',
        'returned_at',
        'due_date',
        'reminder_sent',
        'last_reminder_at',
    ];

    protected $casts = [
        'status' => 'string',
        'borrowed_at' => 'datetime',
        'returned_at' => 'datetime',
        'due_date' => 'date',
        'reminder_sent' => 'boolean',
        'last_reminder_at' => 'datetime',
    ];

    /**
     * Get the book being requested.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the user borrowing the book.
     */
    public function borrower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    /**
     * Get the user who owns the book.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get messages for this book request.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get ratings for this book request.
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get disputes for this book request.
     */
    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }

    /**
     * Get the book being offered for swap.
     */
    public function swapBook(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'swap_book_id');
    }
}
