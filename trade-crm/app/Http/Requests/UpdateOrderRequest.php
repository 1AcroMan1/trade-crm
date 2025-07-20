<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function rules()
    {
        return [
            'customer' => 'sometimes|required|string|max:255',
            'warehouse_id' => 'sometimes|required|exists:warehouses,id',
            'items' => 'sometimes|required|array|min:1',
            'items.*.id' => 'sometimes|required|exists:order_items,id,order_id,' . $this->route('order')->id,
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.count' => 'required_with:items|integer|min:1'
        ];
    }

    public function messages()
    {
        return [
            'items.*.product_id.required' => 'Для каждой позиции укажите товар',
            'items.*.count.min' => 'Минимальное количество: 1',
            'items.*.id.exists' => 'Одна из позиций не принадлежит этому заказу'
        ];
    }
}
