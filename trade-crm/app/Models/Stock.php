<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ['product_id', 'warehouse_id', 'stock'];
    public $incrementing = false;
    protected $primaryKey = ['product_id', 'warehouse_id'];

    //Товар
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    //Склад
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
