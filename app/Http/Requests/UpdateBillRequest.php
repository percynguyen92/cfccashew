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
            'bill_number.required' => __('validation.custom.bill_number.required'),
            'bill_number.max' => __('validation.custom.bill_number.max'),
            'seller.required' => __('validation.custom.seller.required'),
            'seller.max' => __('validation.custom.seller.max'),
            'buyer.required' => __('validation.custom.buyer.required'),
            'buyer.max' => __('validation.custom.buyer.max'),
        ];
    }
}