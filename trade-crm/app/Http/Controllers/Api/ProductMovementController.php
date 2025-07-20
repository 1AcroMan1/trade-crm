<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class ProductMovementController extends Controller
{
    public function index(Request $request)
    {
        $completedOrders = Order::with(['items.product', 'warehouse'])
            ->where('status', Order::STATUS_COMPLETED)
            ->orderBy('id', 'desc')
            ->paginate(15);

        $movements = $completedOrders->map(function ($order) {
            return [
                'order_id' => $order->id,
                'customer' => $order->customer,
                'warehouse' => $order->warehouse->name,
                'completed_at' => $order->completed_at,
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_name' => $item->product->name,
                        'quantity' => $item->count,
                        'price' => $item->product->price,
                        'total' => $item->count * $item->product->price
                    ];
                })
            ];
        });

        return response()->json([
            'data' => $movements,
            'meta' => [
                'current_page' => $completedOrders->currentPage(),
                'total' => $completedOrders->total(),
                'per_page' => $completedOrders->perPage(),
                'last_page' => $completedOrders->lastPage()
            ]
        ]);
    }
}
