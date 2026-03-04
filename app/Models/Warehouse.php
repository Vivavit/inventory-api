<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'contact_person',
        'phone',
        'email',
        'type',
        'capacity',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function inventoryLocations()
    {
        return $this->hasMany(InventoryLocation::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'warehouse_users')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function assignedUsers()
    {
        return $this->users()->where('user_type', 'staff');
    }
}
