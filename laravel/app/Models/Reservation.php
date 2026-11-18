<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'customer_id',
        'table_id',
        'reserved_at',
        'guests_count',
        'status',
    ];

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['id'] ?? null, fn(Builder $q, $id) => $q->where('id', (int) $id))
            ->when($filters['customer_id'] ?? null, fn(Builder $q, $cid) => $q->where('customer_id', (int) $cid))
            ->when($filters['table_id'] ?? null, fn(Builder $q, $tid) => $q->where('table_id', (int) $tid))
            ->when($filters['reserved_at'] ?? null, fn(Builder $q, $date) => $q->where('reserved_at', 'like', "{$date}%"))
            ->when($filters['guests_count'] ?? null, fn(Builder $q, $guests) => $q->where('guests_count', (int) $guests))
            ->when($filters['status'] ?? null, fn(Builder $q, $status) => $q->where('status', $status));
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }
}