<?php

declare(strict_types=1);

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'billNumber' => ['required', 'string', 'max:20'],
            'seller' => ['nullable', 'string', 'max:255'],
            'buyer' => ['nullable', 'string', 'max:255'],
        ];
    }
}