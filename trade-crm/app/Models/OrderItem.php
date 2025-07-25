<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'product_id', 'count'];

    // Заказ к которому относится позиция
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Товар в позиции
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
