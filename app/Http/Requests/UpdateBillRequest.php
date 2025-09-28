<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBillRequest extends FormRequest
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
            'bill_number' => [
                'required',
                'string',
                'max:20',
            ],
            'seller' => 'required|string|max:255',
            'buyer' => 'required|string|max:255',
            'note' => 'nullable|string|max:65535',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'bill_number.required' => 'Bill number is required.',
            'bill_number.max' => 'Bill number cannot exceed 20 characters.',
            'seller.required' => 'Seller is required.',
            'seller.max' => 'Seller name cannot exceed 255 characters.',
            'buyer.required' => 'Buyer is required.',
            'buyer.max' => 'Buyer name cannot exceed 255 characters.',
        ];
    }
}