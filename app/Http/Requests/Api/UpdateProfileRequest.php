<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore(Auth::id())],
            'title'           => ['nullable', 'string', 'max:255'],
            'bio'             => ['nullable', 'string', 'max:1000'],
            'hourly_rate'     => ['nullable', 'numeric', 'min:0'],
            'phone_number'    => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone_number')->ignore(Auth::id())],
            'profile_image'   => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'availability'    => ['nullable', 'boolean'],
            'portfolio_links' => ['nullable', 'array'],
            'portfolio_links.*' => ['url'],
        ];
    }
}
