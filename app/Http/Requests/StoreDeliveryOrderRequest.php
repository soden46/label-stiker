<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delivery_date' => ['required', 'date'],
            'to_partner_id' => ['required', 'integer', 'exists:business_partners,id'],
            'ship_to_partner_id' => ['required', 'integer', 'exists:business_partners,id'],
            'to_project_site' => ['nullable', 'string', 'max:160'],
            'to_address' => ['required', 'string', 'max:2000'],
            'ship_to_project_site' => ['nullable', 'string', 'max:160'],
            'ship_to_address' => ['required', 'string', 'max:2000'],
            'ship_to_phone' => ['nullable', 'string', 'max:40'],
            'purchase_order_no' => ['nullable', 'string', 'max:100'],
            'fob' => ['nullable', 'string', 'max:100'],
            'packages' => ['nullable', 'string', 'max:100'],
            'ship_via' => ['nullable', 'string', 'max:100'],
            'shipment' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'items.*.unit' => ['required', 'string', 'max:20'],
            'items.*.weight' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
        ];
    }

    public function attributes(): array
    {
        return [
            'delivery_date' => 'tanggal DO',
            'to_partner_id' => 'customer tujuan',
            'ship_to_partner_id' => 'customer penerima',
            'to_address' => 'alamat tujuan',
            'ship_to_address' => 'alamat penerima',
            'items' => 'item DO',
            'items.*.product_id' => 'produk',
            'items.*.quantity' => 'qty',
            'items.*.unit' => 'unit',
            'items.*.weight' => 'weight',
        ];
    }
}
