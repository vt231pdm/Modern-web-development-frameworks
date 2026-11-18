<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantTable extends Model
{
    protected $fillable = [
        'table_number',
        'seats',
        'zone',
        'is_available',
    ];

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (isset($filters['is_available']) && $filters['is_available'] !== '') {
            $query->where('is_available', filter_var($filters['is_available'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query
            ->when($filters['id'] ?? null, fn(Builder $q, $id) => $q->where('id', (int) $id))
            ->when($filters['table_number'] ?? null, fn(Builder $q, $num) => $q->where('table_number', (int) $num))
            ->when($filters['seats'] ?? null, fn(Builder $q, $seats) => $q->where('seats', (int) $seats))
            ->when($filters['zone'] ?? null, fn(Builder $q, $zone) => $q->where('zone', 'like', "%{$zone}%"));
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'table_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_id');
    }
}