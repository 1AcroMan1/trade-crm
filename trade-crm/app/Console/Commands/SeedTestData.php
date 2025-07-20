<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Order;
use App\Models\OrderItem;
use Faker\Factory as Faker;

class SeedTestData extends Command
{
    protected $signature = 'db:LoadData';
    protected $description = 'Seeder';

    public function handle()
    {
        $faker = Faker::create('ru_RU');

        $this->info('Начало наполнения базы тестовыми данными...');

        // Очищаем таблицы с учетом внешних ключей
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        OrderItem::truncate();
        Order::truncate();
        Stock::truncate();
        Product::truncate();
        Warehouse::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Склады
        $warehousesData = [
            ['name' => 'Основной склад'],
            ['name' => 'Дополнительный склад'],
            ['name' => 'Северный склад'],
            ['name' => 'Южный склад'],
        ];

        $warehouses = [];
        foreach ($warehousesData as $data) {
            $warehouses[] = Warehouse::create($data);
        }
        $this->info('Создано складов: ' . count($warehouses));

        // Товары
        $productsData = [
            ['name' => 'Ноутбук Lenovo', 'price' => 45000],
            ['name' => 'Мышь Logitech', 'price' => 2500],
            ['name' => 'Клавиатура Defender', 'price' => 3200],
            ['name' => 'Монитор Samsung', 'price' => 28000],
            ['name' => 'Наушники Sony', 'price' => 7500],
            ['name' => 'Флешка 32GB', 'price' => 800],
            ['name' => 'Жесткий диск 1TB', 'price' => 5000],
            ['name' => 'Роутер TP-Link', 'price' => 3500],
            ['name' => 'Тест', 'price' => 12000],
        ];

        $products = [];
        foreach ($productsData as $data) {
            $products[] = Product::create($data);
        }
        $this->info('Создано товаров: ' . count($products));

        // Остатки на складах
        foreach ($products as $product) {
            foreach ($warehouses as $warehouse) {
                Stock::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'stock' => $faker->numberBetween(0, 100),
                ]);
            }
        }
        $this->info('Создано записей об остатках: ' . count($products) * count($warehouses));

        // Заказы
        $statuses = ['active', 'completed', 'canceled'];
        
        for ($i = 0; $i < 20; $i++) {
            $warehouseIndex = $faker->numberBetween(0, count($warehouses) - 1);
            $order = Order::create([
                'customer' => $faker->name,
                'warehouse_id' => $warehouses[$warehouseIndex]->id,
                'status' => $statuses[$faker->numberBetween(0, 2)],
                'created_at' => $faker->dateTimeBetween('-2 months'),
                'completed_at' => $faker->optional(0.3)->dateTimeBetween('-1 month') // 30% шанс на отменённый
            ]);

            // Позиции в заказ (1-5 товаров в каждом заказе)
            $itemsCount = $faker->numberBetween(1, 5);
            $usedProducts = [];

            for ($j = 0; $j < $itemsCount; $j++) {
                $product = $products[$faker->numberBetween(0, count($products)-1)];
                
                while (in_array($product->id, $usedProducts)) {
                    $product = $products[$faker->numberBetween(0, count($products)-1)];
                }
                
                $usedProducts[] = $product->id;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'count' => $faker->numberBetween(1, 10),
                ]);
            }
        }
        $this->info('Создано заказов: 20 с позициями');
        $this->info('Наполнение базы тестовыми данными завершено');
    }
}
