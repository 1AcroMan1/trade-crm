<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    //Товары на этом складе (через stocks)
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'stocks')
            ->withPivot('stock')
            ->withTimestamps();
    }
    //Заказы для этого склада
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
    //Остатки товаров на складе
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
}
