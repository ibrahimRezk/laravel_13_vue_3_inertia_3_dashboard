<?php

namespace App\Http\Requests;

use CodeZero\UniqueTranslation\UniqueTranslationRule;
use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'name.ar' => ['bail', 'required', 'string', 'max:255'],
            'name.en' => ['bail', 'required', 'string', 'max:255'],
            'address.ar' => ['bail', 'required', 'string', 'max:255'],
            'address.en' => ['bail', 'required', 'string', 'max:255'],
            'active' => ['bail', 'required', 'boolean'],
            'phone' => ['bail',  'required', 'string', 'max:255'],
            'email' => ['bail', 'required', 'string', 'email', 'max:255'],
            'weekendDays' => ['bail', 'nullable', 'array'],
            // 'vacationsDates' => ['bail', 'nullable', 'array'],

            'logo' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ];
    }
}



