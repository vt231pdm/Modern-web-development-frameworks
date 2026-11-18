<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [
        'name',
        'category',
        'description',
        'price',
        'is_active',
    ];

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query
            ->when($filters['id'] ?? null, fn(Builder $q, $id) => $q->where('id', (int) $id))
            ->when($filters['name'] ?? null, fn(Builder $q, $name) => $q->where('name', 'like', "%{$name}%"))
            ->when($filters['category'] ?? null, fn(Builder $q, $cat) => $q->where('category', 'like', "%{$cat}%"))
            ->when($filters['description'] ?? null, fn(Builder $q, $desc) => $q->where('description', 'like', "%{$desc}%"))
            ->when($filters['price'] ?? null, fn(Builder $q, $price) => $q->where('price', $price));
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}