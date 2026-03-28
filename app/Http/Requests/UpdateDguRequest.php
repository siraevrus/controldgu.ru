<?php

namespace App\Http\Requests;

use App\Models\Dgu;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDguRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Dgu $dgu */
        $dgu = $this->route('dgu');

        return $this->user()?->can('update', $dgu) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Dgu $dgu */
        $dgu = $this->route('dgu');

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['required', 'string', 'max:255', Rule::unique('dgus', 'serial_number')->ignore($dgu->id)],
            'address' => ['nullable', 'string', 'max:2000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'responsible_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:64'],
            'nominal_power_kw' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'model_name' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'tags_input' => ['nullable', 'string', 'max:2000'],
            'is_manually_disabled' => ['boolean'],
            'operational_state' => ['required', Rule::in(['running', 'stopped'])],
        ];
    }
}
