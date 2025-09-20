<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContainerRequest extends FormRequest
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
            'bill_id.required' => 'Bill ID is required.',
            'bill_id.exists' => 'The selected bill does not exist.',
            'container_number.size' => 'Container number must be exactly 11 characters.',
            'container_number.regex' => 'Container number must follow ISO format (4 letters + 7 digits).',
            'w_jute_bag.max' => 'Jute bag weight cannot exceed 99.99 kg.',
            '*.min' => 'Weight values cannot be negative.',
        ];
    }
}