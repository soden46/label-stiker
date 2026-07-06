<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLabelPrintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'purchase_order_no' => ['required', 'string', 'max:100'],
            'customer_part_no' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99999999'],
            'uom' => ['required', 'string', 'max:20'],
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id' => 'part/SKU',
            'purchase_order_no' => 'nomor PO',
            'customer_part_no' => 'customer part number',
            'quantity' => 'jumlah',
            'uom' => 'satuan',
        ];
    }
}
