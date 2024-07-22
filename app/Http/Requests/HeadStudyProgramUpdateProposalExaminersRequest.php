<?php

namespace App\Http\Requests;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class HeadStudyProgramUpdateProposalExaminersRequest extends FormRequest
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
            'proposal_id' => ["required"],
            'new_examiner' => ["required"],
            'old_examiner' => ["required"],
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
    }
}