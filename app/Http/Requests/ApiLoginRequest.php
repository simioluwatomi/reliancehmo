<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class ApiLoginRequest extends FormRequest
{
    public function __construct()
    {
        if (!empty($this->all())) {
            return response()->json(
                [
                    'status'  => 'error',
                    'message' => 'No body found in request',
                ],
                400,
                $this->headers
            );
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'required|string',
            'password' => 'required|string',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'username.required' => 'Kindly provide your email address or hmo id.',
            'password.required' => 'Kindly provide your password.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json(
            [
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ],
            400,
            $this->headers
        );

        throw new ValidationException($validator, $response);
    }
}
