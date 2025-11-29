<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileExtendedUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:20'],
            'province' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'The phone field is required.',
            'phone.string' => 'The phone must be a string.',
            'phone.max' => 'The phone may not be greater than 20 characters.',
            
            'province.required' => 'The province field is required.',
            'province.string' => 'The province must be a string.',
            'province.max' => 'The province may not be greater than 255 characters.',
            
            'district.required' => 'The district field is required.',
            'district.string' => 'The district must be a string.',
            'district.max' => 'The district may not be greater than 255 characters.',
            
            'neighborhood.required' => 'The neighborhood field is required.',
            'neighborhood.string' => 'The neighborhood must be a string.',
            'neighborhood.max' => 'The neighborhood may not be greater than 255 characters.',
            
            'street.required' => 'The street field is required.',
            'street.string' => 'The street must be a string.',
            'street.max' => 'The street may not be greater than 255 characters.',
        ];
    }
}