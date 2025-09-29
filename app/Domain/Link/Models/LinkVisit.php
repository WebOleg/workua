<?php

namespace App\Domain\Link\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * LinkVisit Model
 * 
 * Tracks analytics data for each visit to a shortened link
 * 
 * @property int $id
 * @property int $link_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $referer
 * @property string|null $country
 * @property string|null $city
 * @property string|null $device_type
 * @property string|null $browser
 * @property string|null $os
 * @property \Carbon\Carbon $visited_at
 */
class LinkVisit extends Model
{
    use HasFactory;

    /**
     * The table associated with the model
     * 
     * @var string
     */
    protected $table = 'link_visits';

    /**
     * Indicates if the model should be timestamped
     * 
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable
     * 
     * @var array<string>
     */
    protected $fillable = [
        'link_id',
        'ip_address',
        'user_agent',
        'referer',
        'country',
        'city',
        'device_type',
        'browser',
        'os',
        'visited_at',
    ];

    /**
     * The attributes that should be cast
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'visited_at' => 'datetime',
    ];

    /**
     * Get the link that owns this visit
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }

    /**
     * Scope to get visits within date range
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('visited_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get visits from today
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToday($query)
    {
        return $query->whereDate('visited_at', today());
    }

    /**
     * Scope to get visits from specific country
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $country
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFromCountry($query, $country)
    {
        return $query->where('country', $country);
    }
}
