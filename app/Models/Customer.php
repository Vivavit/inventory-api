<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'phone',
        'email',
        'name',
        'address',
        'loyalty_points',
        'total_spent',
    ];

    protected $casts = [
        'loyalty_points' => 'integer',
        'total_spent' => 'decimal:2',
    ];

    /**
     * Get the orders for the customer.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
