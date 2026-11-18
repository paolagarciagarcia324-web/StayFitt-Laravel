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
            'titular' => ['required', 'string', 'max:120'],
            'pago_tipo_documento' => ['required', 'in:CC,CE,NIT,PP,TI,PASAPORTE,OTRO'],
            'pago_documento' => ['required', 'string', 'max:30'],
            'pago_correo' => ['required', 'email', 'max:150'],
            'pago_telefono' => ['required', 'string', 'max:30'],
            'ciudad_pago' => ['required', 'string', 'max:80'],
            'direccion_pago' => ['nullable', 'string', 'max:150'],
            'banco_pse' => ['required_if:metodo_pago,PSE', 'nullable', 'string', 'max:80'],
            'pago_mismos_datos' => ['nullable', 'boolean'],
            'acepto_recurrente' => ['nullable', 'boolean'],
        ];
    }

    protected function mensajesPagoSimulado(): array
    {
        return [
            'metodo_pago.required' => 'Elige cómo vas a pagar.',
            'metodo_pago.in' => 'El método de pago no es válido.',
            'numero_tarjeta.required_if' => 'Ingresa el número de la tarjeta.',
            'numero_tarjeta.regex' => 'El número de tarjeta debe tener 15 o 16 dígitos.',
            'vencimiento.required_if' => 'Ingresa el vencimiento (MM/AA).',
            'vencimiento.regex' => 'El formato de vencimiento debe ser MM/AA.',
            'cvv.required_if' => 'Ingresa el código de seguridad.',
            'cvv.regex' => 'El código de seguridad debe tener 3 o 4 dígitos.',
            'titular.required' => 'Ingresa el nombre del titular o pagador.',
            'titular.required_if' => 'Ingresa el nombre del titular o pagador.',
            'ciudad_pago.required' => 'Ingresa la ciudad del pagador.',
            'ciudad_pago.required_if' => 'Ingresa la ciudad del pagador.',
            'pago_tipo_documento.required' => 'Selecciona el tipo de documento del pagador.',
            'pago_tipo_documento.in' => 'El tipo de documento del pagador no es válido.',
            'pago_documento.required' => 'Ingresa el documento para validar el pago.',
            'pago_correo.required' => 'Ingresa el correo para el comprobante de pago.',
            'pago_correo.email' => 'Ingresa un correo electrónico válido para el pago.',
            'pago_telefono.required' => 'Ingresa el teléfono para el pago.',
            'banco_pse.required_if' => 'Selecciona tu banco para PSE.',
        ];
    }

    protected function prepararPagoSimulado(): void
    {
        $merge = [
            'acepto_recurrente' => $this->boolean('acepto_recurrente'),
            'pago_mismos_datos' => $this->boolean('pago_mismos_datos'),
        ];

        if ($this->filled('numero_tarjeta')) {
            $merge['numero_tarjeta'] = preg_replace('/\D+/', '', (string) $this->input('numero_tarjeta'));
        }

        // Si y SOLO si el usuario seleccionó explícitamente reutilizar sus datos personales para el pago,
        // autocompletamos los campos del pagador que hayan quedado vacíos.
        // Si la opción NO fue marcada, los datos del pagador se tratan como entidad independiente
        // (esposo, padres, familiar, empresa, etc.) y jamás se sobreescriben ni se fuerzan con los datos del alumno.
        if ($this->boolean('pago_mismos_datos')) {
            if (! $this->filled('pago_documento') && $this->filled('documento_identidad')) {
                $merge['pago_documento'] = (string) $this->input('documento_identidad');
            }

            if (! $this->filled('pago_correo') && $this->filled('correo')) {
                $merge['pago_correo'] = (string) $this->input('correo');
            }

            if (! $this->filled('pago_telefono') && $this->filled('telefono')) {
                $merge['pago_telefono'] = (string) $this->input('telefono');
            }

            if (! $this->filled('ciudad_pago') && $this->filled('ciudad')) {
                $merge['ciudad_pago'] = (string) $this->input('ciudad');
            }

            if (! $this->filled('titular')) {
                $nombreCompleto = trim(($this->input('nombres') ?? '').' '.($this->input('apellidos') ?? ''));
                if ($nombreCompleto !== '') {
                    $merge['titular'] = $nombreCompleto;
                }
            }

            if (! $this->filled('pago_tipo_documento') && $this->filled('tipo_documento')) {
                $merge['pago_tipo_documento'] = match ((string) $this->input('tipo_documento')) {
                    'PASAPORTE', 'PP' => 'PP',
                    'CE' => 'CE',
                    'NIT' => 'NIT',
                    'TI' => 'TI',
                    default => 'CC',
                };
            }
        }

        $this->merge($merge);
    }
}
