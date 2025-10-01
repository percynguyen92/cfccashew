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
            'w_total' => 'nullable|integer|min:0',
            'w_truck' => 'nullable|integer|min:0',
            'w_container' => 'nullable|integer|min:0',
            'w_gross' => 'nullable|integer|min:0',
            'w_tare' => 'nullable|numeric|min:0',
            'w_net' => 'nullable|numeric|min:0',
            'container_condition' => 'nullable|string|max:255',
            'seal_condition' => 'nullable|string|max:255',
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
            '*.min' => __('validation.custom.weights.min'),
        ];
    }
}
