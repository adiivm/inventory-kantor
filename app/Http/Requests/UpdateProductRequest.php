<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use App\Enums\ProductCondition;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('staff-access');
    }

    public function rules(): array
    {
        return [
            'sku'                  => 'required|unique:products,sku,' . $this->route('id'),
            'name'                 => 'required',
            'condition'            => ['required', Rule::enum(ProductCondition::class)],
            'price'                => 'nullable|integer',
            'warranty_expiry_date' => 'nullable|date',
            'purchase_date'        => 'nullable|date',
        ];
    }
}
