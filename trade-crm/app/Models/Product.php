<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price'];

    //Склады, где есть этот товар (через таблицу stocks)
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'stocks')
            ->withPivot('stock')
            ->withTimestamps();
    }
    //Позиции в заказах с этим товаром
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    //Остатки на складах
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
}
