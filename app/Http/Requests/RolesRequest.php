<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\RoleTranslation;
use CodeZero\UniqueTranslation\UniqueTranslationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RolesRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $model = $this->route('role');
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(Role::class)->ignore($model->id ?? null)],


        'slug.*' => ['bail', 'required', 'string', 'max:255' ,UniqueTranslationRule::for('roles', 'slug')->ignore($model->id ?? null)],

        ];
    }
}
