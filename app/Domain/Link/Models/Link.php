<?php

namespace App\Domain\Link\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Link Model
 * 
 * Represents a shortened URL with its metadata and expiration settings
 * 
 * @property int $id
 * @property string $original_url
 * @property string $short_code
 * @property \Carbon\Carbon|null $expires_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Link extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model
     * 
     * @var string
     */
    protected $table = 'links';

    /**
     * The attributes that are mass assignable
     * 
     * @var array<string>
     */
    protected $fillable = [
        'original_url',
        'short_code',
        'expires_at',
    ];

    /**
     * The attributes that should be cast
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get all visits for this link
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function visits(): HasMany
    {
        return $this->hasMany(LinkVisit::class);
    }

    /**
     * Check if the link has expired
     * 
     * @return bool Returns true if link is expired, false otherwise
     */
    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Check if the link is still active (not expired and not deleted)
     * 
     * @return bool Returns true if link is active
     */
    public function isActive(): bool
    {
        return !$this->isExpired() && $this->deleted_at === null;
    }

    /**
     * Get the total number of visits for this link
     * 
     * @return int Total visit count
     */
    public function getVisitsCount(): int
    {
        return $this->visits()->count();
    }

    /**
     * Get unique visitors count (based on IP address)
     * 
     * @return int Unique visitor count
     */
    public function getUniqueVisitorsCount(): int
    {
        return $this->visits()->distinct('ip_address')->count('ip_address');
    }

    /**
     * Get the short URL for this link
     * 
     * @return string Full short URL
     */
    public function getShortUrl(): string
    {
        return config('app.url') . '/' . $this->short_code;
    }

    /**
     * Scope to get only active links (not expired, not deleted)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        })->whereNull('deleted_at');
    }

    /**
     * Scope to get expired links
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now());
    }
}
