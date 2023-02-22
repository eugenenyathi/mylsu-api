<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'studentID' => ['required', 'string', 'max:9', 'regex: /^L0\d{6}[A-Z]{1}$/u'],
            'password' => ['required', 'string', 'min:8']
        ];
    }
}
