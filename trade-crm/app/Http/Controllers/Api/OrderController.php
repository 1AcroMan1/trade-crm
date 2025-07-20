<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    const PER_PAGE = 15;
    public function index(Request $request)
    {
        try {
            $query = Order::with([
                'warehouse:id,name',
                'items.product:id,name,price'
            ]);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('warehouse_id')) {
                $query->where('warehouse_id', $request->warehouse_id);
            }

            $sortDirection = $request->input('sort_direction', 'desc');
            $query->orderBy('id', $sortDirection);

            $perPage = $request->input('per_page', 15);
            $orders = $query->paginate($perPage);

            return response()->json($orders);


        } catch (\Exception $e) {
            \Log::error('Order API Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Server Error',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

     public function checkRelations()
    {
        $order = Order::with(['warehouse', 'items', 'products'])->first();
        
        return response()->json([
            'order' => $order,
            'items_count' => $order ? $order->items->count() : 0,
            'products_count' => $order ? $order->products->count() : 0,
            'items_vs_products' => $order ? ($order->items->count() === $order->products->count()) : null,
            'all_orders_count' => Order::count()
        ]);
    }

    public function show($id)
    {
        try {
            $order = Order::with(['warehouse', 'items.product'])->findOrFail($id);

            return response()->json([
                'id' => $order->id,
                'customer' => $order->customer,
                'warehouse_id' => $order->warehouse_id,
                'status' => $order->status,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'count' => $item->count,
                        'product' => $item->product
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Order not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    //Создать заказ
    public function store(StoreOrderRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            foreach ($request->items as $item) {
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $request->warehouse_id)
                    ->firstOrFail();

                if ($stock->stock < $item['count']) {
                    return response()->json([
                        'error' => 'Недостаточно товара на складе',
                        'product_id' => $item['product_id'],
                        'available' => $stock->stock,
                        'requested' => $item['count']
                    ], 422);
                }
            }

            // Создание заказа
            $order = Order::create([
                'customer' => $request->customer,
                'warehouse_id' => $request->warehouse_id,
                'status' => Order::STATUS_ACTIVE,
            ]);

            // Добавление позиций и резервирование товаров
            foreach ($request->items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'count' => $item['count'],
                ]);

                Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $request->warehouse_id)
                    ->decrement('stock', $item['count']);
            }

            return response()->json([
                'message' => 'Заказ успешно создан',
                'data' => $order->load(['items', 'warehouse'])
            ], 201);
        });
    }

    //Обновление заказа
    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        if ($order->status !== Order::STATUS_ACTIVE) {
            return response()->json([
                'error' => 'Order not active',
                'message' => 'Можно обновлять только активные заказы'
            ], 422);
        }

        \Log::debug('Order update request', $request->all());

        return DB::transaction(function () use ($request, $order) {
            try {
                $order->update($request->only(['customer', 'warehouse_id']));

                if ($request->has('items')) {
                    $this->updateOrderItems($order, $request->items);
                }

                return response()->json([
                    'message' => 'Заказ успешно обновлен',
                    'data' => $order->load(['items.product', 'warehouse'])
                ]);

            } catch (\Exception $e) {
                \Log::error('Order update failed: ' . $e->getMessage());
                return response()->json([
                    'error' => 'Update failed',
                    'message' => $e->getMessage()
                ], 500);
            }
        });
    }

    //ЗАвершение заказа
    public function complete(Order $order): JsonResponse
    {
        if ($order->status !== Order::STATUS_ACTIVE) {
            return response()->json(['error' => 'Заказ не может быть завершен'], 422);
        }

        $order->update([
            'status' => Order::STATUS_COMPLETED,
            'completed_at' => now()
        ]);

        return response()->json(['message' => 'Заказ завершен']);
    }

    //Обновление позиций заказа
    protected function updateOrderItems(Order $order, array $items): void
    {
        $currentItems = $order->items->keyBy('product_id');
        $warehouseId = $order->warehouse_id;

        foreach ($items as $item) {
            if (isset($item['_delete']) && isset($item['id'])) {
                $orderItem = $order->items()->find($item['id']);
                if ($orderItem) {
                    Stock::where('product_id', $orderItem->product_id)
                        ->where('warehouse_id', $warehouseId)
                        ->increment('stock', $orderItem->count);

                    $orderItem->delete();
                }
                continue;
            }

            // Обновление существующей позиции
            if (isset($item['id']) && $currentItems->has($item['id'])) {
                $orderItem = $currentItems->get($item['id']);
                $diff = $item['count'] - $orderItem->count;

                if ($diff != 0) {
                    $stock = Stock::where('product_id', $orderItem->product_id)
                        ->where('warehouse_id', $warehouseId)
                        ->first();

                    if ($stock->stock >= $diff) {
                        $stock->decrement('stock', $diff);
                        $orderItem->update(['count' => $item['count']]);
                    }
                }
                continue;
            }

            // Добавление новой позиции
            if (!isset($item['id'])) {
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                if ($stock && $stock->stock >= $item['count']) {
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'count' => $item['count'],
                    ]);
                    $stock->decrement('stock', $item['count']);
                }
            }
        }
    }
}
