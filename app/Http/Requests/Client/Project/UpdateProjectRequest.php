<?php

namespace App\Http\Requests\Client\Project;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role ==='client';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'min:10', 'max:255'],
            'description'  => ['required', 'string', 'min:30'],
            'budget'       => ['required', 'numeric', 'min:10'],
            'deadline'     => ['required', 'date', 'after:today'],
            'tags'       => ['required', 'array', 'min:1','max:5'],
            'tags.*'     => ['exists:tags,id'],
        ];
    }
}
