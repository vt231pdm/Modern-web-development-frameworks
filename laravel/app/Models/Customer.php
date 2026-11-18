<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'full_name',
        'phone',
        'email',
    ];

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['id'] ?? null, fn(Builder $q, $id) => $q->where('id', (int) $id))
            ->when($filters['full_name'] ?? null, fn(Builder $q, $name) => $q->where('full_name', 'like', "%{$name}%"))
            ->when($filters['phone'] ?? null, fn(Builder $q, $phone) => $q->where('phone', 'like', "%{$phone}%"))
            ->when($filters['email'] ?? null, fn(Builder $q, $email) => $q->where('email', 'like', "%{$email}%"))
            ->when($filters['created_at'] ?? null, fn(Builder $q, $date) => $q->whereDate('created_at', $date));
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}