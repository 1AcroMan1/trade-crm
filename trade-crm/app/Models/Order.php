<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELED = 'canceled';

    use HasFactory;

    protected $fillable = ['customer', 'status', 'warehouse_id', 'completed_at'];
    protected $dates = ['completed_at'];

    // Склад заказа
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    // Позиции заказа
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->with('product');
    }
}
