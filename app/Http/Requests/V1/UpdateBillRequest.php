<?php

declare(strict_types=1);

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'billNumber' => ['sometimes', 'required', 'string', 'max:20'],
            'seller' => ['sometimes', 'nullable', 'string', 'max:255'],
            'buyer' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}