<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'table_id',
        'menu_item_id',
        'quantity',
        'total_price',
        'status',
    ];

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['id'] ?? null, fn(Builder $q, $id) => $q->where('id', (int) $id))
            ->when($filters['customer_id'] ?? null, fn(Builder $q, $cid) => $q->where('customer_id', (int) $cid))
            ->when($filters['table_id'] ?? null, fn(Builder $q, $tid) => $q->where('table_id', (int) $tid))
            ->when($filters['menu_item_id'] ?? null, fn(Builder $q, $mid) => $q->where('menu_item_id', (int) $mid))
            ->when($filters['quantity'] ?? null, fn(Builder $q, $qty) => $q->where('quantity', (int) $qty))
            ->when($filters['total_price'] ?? null, fn(Builder $q, $price) => $q->where('total_price', $price))
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

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}