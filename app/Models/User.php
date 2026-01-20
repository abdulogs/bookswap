<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the books owned by the user.
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'user_id');
    }

    /**
     * Get the book requests where the user is the borrower.
     */
    public function borrowingRequests(): HasMany
    {
        return $this->hasMany(BookRequest::class, 'borrower_id');
    }

    /**
     * Get the book requests where the user is the owner.
     */
    public function lendingRequests(): HasMany
    {
        return $this->hasMany(BookRequest::class, 'owner_id');
    }

    /**
     * Get messages sent by the user.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get messages received by the user.
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Get ratings given by the user.
     */
    public function givenRatings(): HasMany
    {
        return $this->hasMany(Rating::class, 'rater_id');
    }

    /**
     * Get ratings received by the user.
     */
    public function receivedRatings(): HasMany
    {
        return $this->hasMany(Rating::class, 'rated_user_id');
    }

    /**
     * Get notifications for the user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get disputes reported by the user.
     */
    public function reportedDisputes(): HasMany
    {
        return $this->hasMany(Dispute::class, 'reporter_id');
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
