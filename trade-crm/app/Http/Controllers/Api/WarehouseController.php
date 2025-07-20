<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(): JsonResponse
    {
        //Список всех складов
        $warehouses = Warehouse::all();
        return response()->json(['data' => $warehouses]);
    }
}
