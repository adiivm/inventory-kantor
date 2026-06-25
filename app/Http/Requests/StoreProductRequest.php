<?php

namespace App\Http\Requests;

use App\Enums\ProductCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('staff-access');
    }

    public function rules(): array
    {
        return [
            'sku' => 'required|unique:products,sku',
            'name' => 'required',
            'condition' => ['required', Rule::enum(ProductCondition::class)],
            'price' => 'nullable|integer',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
            'warranty_expiry_date' => 'nullable|date',
            'purchase_date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'Waduh Mas Bro, SKU ini sudah dipakai barang lain!',
        ];
    }
}
