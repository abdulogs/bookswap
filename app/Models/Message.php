<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_request_id',
        'sender_id',
        'receiver_id',
        'message',
        'read',
    ];

    protected $casts = [
        'read' => 'boolean',
    ];

    public function bookRequest(): BelongsTo
    {
        return $this->belongsTo(BookRequest::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
