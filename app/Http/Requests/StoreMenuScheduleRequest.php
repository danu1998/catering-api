<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'menu_id' => [
                'required',
                'integer',
                'exists:menus,id',
            ],
            'schedule_date' => [
                'required',
                'date_format:Y-m-d',
                Rule::unique('menu_schedules', 'schedule_date')
                    ->where('menu_id', $this->input('menu_id')),
            ],
            'price_override' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'quota' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }
}
