<?php

namespace App\Http\Requests\Admin;

use App\Services\Analitica\ReporteExportService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminReporteExportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tieneRol('ADMIN') ?? false;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(array_keys(ReporteExportService::TIPOS))],
            'formato' => ['required', Rule::in(['csv', 'excel', 'pdf', 'web'])],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'modalidad' => ['nullable', 'string', 'max:20'],
            'estado' => ['nullable', 'string', 'max:40'],
            'pasarela' => ['nullable', 'string', 'max:30'],
            'id_plan' => ['nullable', 'integer'],
            'id_user' => ['nullable', 'integer'],
            'categoria' => ['nullable', 'string', 'max:40'],
        ];
    }
}
