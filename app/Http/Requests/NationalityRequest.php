<?php

namespace App\Http\Requests;

use App\Models\NationalityTranslation;
use CodeZero\UniqueTranslation\UniqueTranslationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class NationalityRequest extends FormRequest
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
        $model = $this->route('nationality');
        // dd($model);
        return [
            'name.*' => ['bail', 'required', 'string', 'max:255', UniqueTranslationRule::for('nationalities', 'name')->ignore($model?->id)],

        ];
    }
}
