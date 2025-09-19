<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'bill_number' => 'nullable|string|max:20|unique:bills,bill_number',
            'seller' => 'nullable|string|max:255',
            'buyer' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:65535',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'bill_number.unique' => 'This bill number already exists.',
            'bill_number.max' => 'Bill number cannot exceed 20 characters.',
            'seller.max' => 'Seller name cannot exceed 255 characters.',
            'buyer.max' => 'Buyer name cannot exceed 255 characters.',
        ];
    }
}