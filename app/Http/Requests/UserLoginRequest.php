<?php

namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
class UserLoginRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'username' => ['required', 'email'],
            'password' => ['required'],
            'role' => ['required', 'string', 'in:students,supervisors,examiners,coordinators,head-study-programs,academic-administrations'],
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
    }
}