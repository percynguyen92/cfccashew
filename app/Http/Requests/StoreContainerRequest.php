<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ContainerCondition;
use App\Enums\SealCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'quantity_of_bags' => [
                'nullable',
                'integer',
                'min:0',
                'max:2000',
            ],
            'w_total' => [
                'nullable',
                'integer',
                'min:0',
                function ($attribute, $value, $fail) {
                    $wTruck = $this->input('w_truck', 0);
                    $wContainer = $this->input('w_container', 0);
                    if ($value && ($value <= ($wTruck + $wContainer))) {
                        $fail(__('validation.custom.w_total.must_be_greater_than_truck_container'));
                    }
                },
            ],
            'w_truck' => 'nullable|integer|min:0',
            'w_container' => 'nullable|integer|min:0',
            'w_gross' => 'nullable|integer|min:0',
            'w_tare' => 'nullable|numeric|min:0',
            'w_net' => 'nullable|numeric|min:0',
            'container_condition' => [
                'required',
                'string',
                Rule::in(ContainerCondition::values()),
            ],
            'seal_condition' => [
                'required',
                'string',
                Rule::in(SealCondition::values()),
            ],
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
            'container_condition.required' => __('validation.custom.container_condition.required'),
            'container_condition.in' => __('validation.custom.container_condition.in'),
            'seal_condition.required' => __('validation.custom.seal_condition.required'),
            'seal_condition.in' => __('validation.custom.seal_condition.in'),
            'quantity_of_bags.max' => __('validation.custom.quantity_of_bags.max'),
            '*.min' => __('validation.custom.weights.min'),
        ];
    }
}
