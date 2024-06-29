<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\File;

class ProposalCreateRequest extends FormRequest
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
            'title' => ['required', 'max:700', "string"],
            'upload_date' => ['required'],
            'period' => ['required'],
            'proposal_file' => ['required', FIle::types(['pdf'])],
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
    }
}