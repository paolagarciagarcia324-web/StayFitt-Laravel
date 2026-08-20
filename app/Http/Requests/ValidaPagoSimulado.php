<?php

namespace App\Http\Requests;

trait ValidaPagoSimulado
{
    protected function reglasPagoSimulado(): array
    {
        return [
            'metodo_pago' => ['required', 'in:TARJETA,PSE'],
            'numero_tarjeta' => ['required_if:metodo_pago,TARJETA', 'nullable', 'regex:/^[0-9]{15,16}$/'],
            'vencimiento' => ['required_if:metodo_pago,TARJETA', 'nullable', 'regex:/^(0[1-9]|1[0-2])\/[0-9]{2}$/'],
            'cvv' => ['required_if:metodo_pago,TARJETA', 'nullable', 'regex:/^[0-9]{3,4}$/'],
            'titular' => ['required_if:metodo_pago,TARJETA', 'nullable', 'string', 'max:120'],
            'pago_tipo_documento' => ['required', 'in:CC,CE,NIT,PP'],
            'pago_documento' => ['required', 'string', 'max:30'],
            'pago_correo' => ['required', 'email', 'max:150'],
            'pago_telefono' => ['required', 'string', 'max:30'],
            'direccion_pago' => ['required_if:metodo_pago,TARJETA', 'nullable', 'string', 'max:150'],
            'ciudad_pago' => ['required_if:metodo_pago,TARJETA', 'nullable', 'string', 'max:80'],
            'banco_pse' => ['required_if:metodo_pago,PSE', 'nullable', 'string', 'max:80'],
            'acepto_recurrente' => ['accepted'],
        ];
    }

    protected function mensajesPagoSimulado(): array
    {
        return [
            'metodo_pago.required' => 'Elige cómo vas a pagar.',
            'numero_tarjeta.required_if' => 'Ingresa el número de la tarjeta.',
            'numero_tarjeta.regex' => 'El número de tarjeta debe tener 15 o 16 dígitos.',
            'vencimiento.required_if' => 'Ingresa el vencimiento (MM/AA).',
            'cvv.required_if' => 'Ingresa el código de seguridad.',
            'titular.required_if' => 'Ingresa el nombre del titular.',
            'pago_documento.required' => 'Ingresa el documento para validar el pago.',
            'banco_pse.required_if' => 'Selecciona tu banco para PSE.',
            'acepto_recurrente.accepted' => 'Debes autorizar el cobro recurrente del plan.',
        ];
    }

    protected function prepararPagoSimulado(): void
    {
        if ($this->filled('numero_tarjeta')) {
            $this->merge([
                'numero_tarjeta' => preg_replace('/\D+/', '', (string) $this->input('numero_tarjeta')),
            ]);
        }
    }
}
