<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Rating;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'genre',
        'condition',
        'status',
        'description',
        'location',
        'image',
        'user_id',
    ];

    protected $casts = [
        'status' => 'string',
        'condition' => 'string',
    ];

    /**
     * Get the user that owns the book.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the book requests for this book.
     */
    public function requests(): HasMany
    {
        return $this->hasMany(BookRequest::class);
    }

    /**
     * Scope to search books by location.
     */
    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    /**
     * Scope to filter available books.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'Available');
    }

    /**
     * Scope to search books by title or author.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('author', 'like', "%{$search}%");
        });
    }

    /**
     * Get ratings for this book (ratings given to the book owner as lender).
     */
    public function ratings()
    {
        return Rating::whereHas('bookRequest', function ($query) {
            $query->where('book_id', $this->id)
                  ->where('status', 'Returned');
        })->where('type', 'lender');
    }

    /**
     * Get the average rating for this book.
     */
    public function getAverageRatingAttribute()
    {
        $ratings = $this->ratings()->pluck('rating');
        
        if ($ratings->isEmpty()) {
            return null;
        }
        
        return round($ratings->avg(), 1);
    }

    /**
     * Get the total number of ratings for this book.
     */
    public function getTotalRatingsAttribute()
    {
        return $this->ratings()->count();
    }
}
