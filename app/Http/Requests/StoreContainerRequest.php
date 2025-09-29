<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContainerRequest extends FormRequest
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
            'bill_id' => 'required|integer|exists:bills,id',
            'truck' => 'nullable|string|max:20',
            'container_number' => [
                'nullable',
                'string',
                'size:11',
                'regex:/^[A-Z]{4}[0-9]{7}$/',
            ],
            'quantity_of_bags' => 'nullable|integer|min:0',
            'w_jute_bag' => 'nullable|numeric|min:0|max:99.99',
            'w_total' => 'nullable|integer|min:0',
            'w_truck' => 'nullable|integer|min:0',
            'w_container' => 'nullable|integer|min:0',
            'w_dunnage_dribag' => 'nullable|integer|min:0',
            'note' => 'nullable|string|max:65535',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'bill_id.required' => __('validation.custom.bill_id.required'),
            'bill_id.integer' => __('validation.custom.bill_id.integer'),
            'bill_id.exists' => __('validation.custom.bill_id.exists'),
            'container_number.size' => __('validation.custom.container_number.size'),
            'container_number.regex' => __('validation.custom.container_number.regex'),
            'w_jute_bag.max' => __('validation.custom.w_jute_bag.max'),
            '*.min' => __('validation.custom.weights.min'),
        ];
    }
}
