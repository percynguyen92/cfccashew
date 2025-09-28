<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CuttingTestType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCuttingTestRequest extends FormRequest
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
                        $fail('Final sample tests cannot be associated with a container.');
                    }
                    
                    // Container tests (4) must have container_id
                    if ($type == 4 && is_null($value)) {
                        $fail('Container tests must be associated with a container.');
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
            'moisture' => 'nullable|numeric|min:0|max:100',
            'sample_weight' => 'required|integer|min:1|max:65535',
            'nut_count' => 'nullable|integer|min:0|max:65535',
            'w_reject_nut' => 'nullable|integer|min:0|max:65535',
            'w_defective_nut' => 'nullable|integer|min:0|max:65535',
            'w_defective_kernel' => 'nullable|integer|min:0|max:65535',
            'w_good_kernel' => 'nullable|integer|min:0|max:65535',
            'w_sample_after_cut' => 'nullable|integer|min:0|max:65535',
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
            'bill_id.required' => 'A valid bill is required.',
            'bill_id.integer' => 'A valid bill is required.',
            'bill_id.exists' => 'The selected bill does not exist.',
            'container_id.exists' => 'The selected container does not exist.',
            'type.required' => 'Select a valid test type.',
            'type.integer' => 'Select a valid test type.',
            'type.in' => 'Select a valid test type.',
            'moisture.min' => 'Moisture cannot be negative.',
            'moisture.max' => 'Moisture cannot exceed 100%.',
            'sample_weight.required' => 'Sample weight must be between 1 and 65,535 grams.',
            'sample_weight.integer' => 'Sample weight must be a whole number.',
            'sample_weight.min' => 'Sample weight must be between 1 and 65,535 grams.',
            'sample_weight.max' => 'Sample weight must be between 1 and 65,535 grams.',
            'outturn_rate.max' => 'Outturn rate cannot exceed 60 lbs/80kg.',
            '*.min' => 'Weight values cannot be negative.',
        ];
    }
}
