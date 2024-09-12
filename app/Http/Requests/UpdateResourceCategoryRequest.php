<?php

namespace App\Http\Requests;

use App\Models\Resource;
use Illuminate\Foundation\Http\FormRequest;

class UpdateResourceCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        dd($this);

        return $this->user()->can('create', Resource::find(request('resource')['id']));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name.lt' => 'required|string',
            'description.lt' => 'nullable|string',
            'name.en' => 'nullable|string',
            'description.en' => 'nullable|string',
            'icon' => 'nullable|string',
        ];
    }
}
