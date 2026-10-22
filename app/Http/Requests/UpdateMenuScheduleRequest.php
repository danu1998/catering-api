<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schedule = $this->route('menuSchedule');
        $scheduleId = $schedule?->id;

        return [
            'menu_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:menus,id',
            ],
            'schedule_date' => [
                'sometimes',
                'required',
                'date_format:Y-m-d',
                Rule::unique('menu_schedules', 'schedule_date')
                    ->where(
                        'menu_id',
                        $this->input('menu_id', $schedule?->menu_id)
                    )
                    ->ignore($scheduleId),
            ],
            'price_override' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ],
            'quota' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
            'notes' => [
                'sometimes',
                'nullable',
                'string',
            ],
        ];
    }
}