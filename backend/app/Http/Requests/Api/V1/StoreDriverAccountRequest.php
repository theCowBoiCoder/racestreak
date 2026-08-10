<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class StoreDriverAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'min:2', 'max:50'],
            'email' => ['required', 'string', 'email:rfc', 'max:254', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'display_name' => is_string($this->input('display_name'))
                ? trim($this->input('display_name'))
                : $this->input('display_name'),
            'email' => is_string($this->input('email'))
                ? Str::lower(trim($this->input('email')))
                : $this->input('email'),
        ]);
    }
}
