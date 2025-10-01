<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CuttingTestType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCuttingTestRequest extends FormRequest
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
            'container_id' => [
                'nullable',
                'integer',
                'exists:containers,id',
                function ($attribute, $value, $fail) {
                    $type = $this->input('type');
                    
                    // Final samples (1-3) should not have container_id
                    if (in_array($type, [1, 2, 3]) && !is_null($value)) {
                        $fail(__('validation.custom.container_id.final_sample_forbidden'));
                    }

                    // Container tests (4) must have container_id
                    if ($type == 4 && is_null($value)) {
                        $fail(__('validation.custom.container_id.container_required'));
                    }
                },
            ],
            'type' => [
                'required',
                'integer',
                Rule::in([
                    CuttingTestType::FinalFirstCut->value,
                    CuttingTestType::FinalSecondCut->value,
                    CuttingTestType::FinalThirdCut->value,
                    CuttingTestType::ContainerCut->value,
                ]),
            ],
            'moisture' => [
                'nullable',
                'numeric',
                'min:0',
                'max:20',
                function ($attribute, $value, $fail) {
                    if ($value && $value > 11) {
                        // This is a warning, not a validation failure
                        // The service layer will handle the alert
                    }
                },
            ],
            'sample_weight' => 'required|integer|min:1|max:65535',
            'nut_count' => 'nullable|integer|min:0|max:65535',
            'w_reject_nut' => 'nullable|integer|min:0|max:65535',
            'w_defective_nut' => 'nullable|integer|min:0|max:65535',
            'w_defective_kernel' => 'nullable|integer|min:0|max:65535',
            'w_good_kernel' => 'nullable|integer|min:0|max:65535',
            'w_sample_after_cut' => [
                'nullable',
                'integer',
                'min:0',
                'max:2000',
                function ($attribute, $value, $fail) {
                    $sampleWeight = $this->input('sample_weight');
                    if ($value && $sampleWeight && ($sampleWeight - $value) > 5) {
                        // This is a warning, not a validation failure
                        // The service layer will handle the alert
                    }
                },
            ],
            'outturn_rate' => 'nullable|numeric|min:0|max:60',
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
            'container_id.exists' => __('validation.custom.container_id.exists'),
            'type.required' => __('validation.custom.type.required'),
            'type.integer' => __('validation.custom.type.integer'),
            'type.in' => __('validation.custom.type.in'),
            'moisture.min' => __('validation.custom.moisture.min'),
            'moisture.max' => __('validation.custom.moisture.max'),
            'sample_weight.required' => __('validation.custom.sample_weight.required'),
            'sample_weight.integer' => __('validation.custom.sample_weight.integer'),
            'sample_weight.min' => __('validation.custom.sample_weight.min'),
            'sample_weight.max' => __('validation.custom.sample_weight.max'),
            'outturn_rate.max' => __('validation.custom.outturn_rate.max'),
            '*.min' => __('validation.custom.weights.min'),
        ];
    }
}
