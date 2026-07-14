<?php

namespace App\Http\Requests\Client\Project;
use App\Rules\ProhibitOffensiveContent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role==='client';
    }

    protected function prepareForValidation(){
        $this->merge([
            'title'=>strip_tags(trim($this->title)),
            'description'=>strip_tags(trim($this->description)),
            'tags'=>is_string($this->tags)?explode(',',$this->tags):$this->tags,
        ]
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'min:10',
                'max:150',
                new ProhibitOffensiveContent()
            ],

            'description' => [
                'required',
                'string',
                'min:50',
                new ProhibitOffensiveContent()
            ],

            'budget_type' => [
                'required',
                'in:fixed,hourly'
            ],

            'budget' => [
                'required',
                'numeric',
                // إذا كان المشروع بميزانية ثابتة، الحد الأدنى مثلاً 20$
                Rule::when($this->budget_type === 'fixed', ['min:20']),
                // إذا كان المشروع بالساعة، السعر بين 5$ و 150$ للساعة مثلاً
                Rule::when($this->budget_type === 'hourly', ['min:5', 'max:150']),
            ],

            'delivery_date' => [
                'required',
                'date',
                'after:now'
            ],

            'tags' => [
                'required',
                'array',
                'max:5'
            ],

            'tags.*' => [
                'string',
                'max:20'
            ],
        ];
    }
}
