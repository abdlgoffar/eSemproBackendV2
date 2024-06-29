<?php

namespace App\Http\Requests;



use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UserCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user() != null;
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
            'password' => ['required', 'max:9'],
            'name' => ['required', 'max:100', "string"],
            'head_study_program_id' => [],
            'address' => ['required', 'max:300'],
            'phone' => ['required', 'numeric', 'unique:students,phone', ],
            'nrp' => [ 'unique:students,nrp'],
            'roles' => ["required", "array", "min:1"],
            'roles.*' => ['string', 'in:students,supervisors,examiners,coordinators,head-study-programs'],
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
    }
}