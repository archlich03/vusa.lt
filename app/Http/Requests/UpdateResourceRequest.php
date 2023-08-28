<?php

namespace App\Http\Requests;

use App\Models\Resource;

class UpdateResourceRequest extends ResourceRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', [Resource::class, $this->authorizer]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name.lt' => 'required|string',
            'description.lt' => 'required|string',
            'name.en' => 'nullable|string',
            'description.en' => 'nullable|string',
            'location' => 'required|string',
            'padalinys_id' => 'required|integer|exists:padaliniai,id',
            'capacity' => 'required|integer|min:1',
            'is_reservable' => 'required|boolean',
            'media' => 'array|nullable',
        ];
    }
}
