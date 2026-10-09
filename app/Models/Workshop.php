<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workshop extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'instructor',
        'start_date',
        'location',
        'description',
        'capacity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'capacity' => 'integer',
        ];
    }

    /**
     * Get all registrations for this workshop.
     */
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Get only active registrations.
     */
    public function activeRegistrations()
    {
        return $this->hasMany(Registration::class)->where('status', 'active');
    }

    /**
     * Count available seats.
     */
    public function getAvailableSeatsAttribute(): int
    {
        return max(0, $this->capacity - $this->active_registrations_count);
    }

    /**
     * Check if the workshop is full.
     */
    public function isFull(): bool
    {
        return $this->activeRegistrations()->count() >= $this->capacity;
    }

    /**
     * Scope: filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: filter by date range.
     */
    public function scopeDateBetween($query, $from, $to)
    {
        if ($from) {
            $query->where('start_date', '>=', $from);
        }
        if ($to) {
            $query->where('start_date', '<=', $to . ' 23:59:59');
        }
        return $query;
    }

    /**
     * Scope: only workshops with available seats.
     */
    public function scopeHasAvailableSeats($query)
    {
        return $query->whereRaw('capacity > (SELECT COUNT(*) FROM registrations WHERE registrations.workshop_id = workshops.id AND registrations.status = ?)', ['active']);
    }
}
