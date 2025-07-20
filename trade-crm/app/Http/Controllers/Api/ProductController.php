<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        //Список товаров с отатками по складам
        $products = Product::with('stocks')->get();
        return response()->json(['data' => $products]);
    }
}
