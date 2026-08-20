<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccesoClienteModulo;
use App\Models\Cliente;
use App\Models\Coach;
use App\Models\HistorialAsignacionCoach;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.clientes.virtuales');
    }

    public function virtuales(Request $request): View
    {
        return $this->listar($request, 'VIRTUAL', 'virtuales', 'Clientes virtuales', 'Individual e institucional');
    }

    public function presenciales(Request $request): View
    {
        return $this->listar($request, 'PRESENCIAL', 'presenciales', 'Clientes presenciales', 'Sala Campoalegre');
    }

    public function show(int $id): View
    {
        $cliente = Cliente::query()
            ->with(['usuario', 'coach.usuario', 'institucion', 'horario', 'planesCliente.plan', 'accesos.modulo', 'rachas'])
            ->findOrFail($id);

        $coaches = Coach::query()->with('usuario')->where('estado_coach', 'ACTIVO')->orderBy('id_coach')->get();

        return view('admin.clientes.show', compact('cliente', 'coaches'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $cliente = Cliente::query()->findOrFail($id);

        $data = $request->validate([
            'ciudad' => ['nullable', 'string', 'max:80'],
            'objetivo_principal' => ['nullable', 'string', 'max:200'],
            'estado_cliente' => ['required', 'string', 'max:30'],
            'telefono' => ['nullable', 'string', 'max:30'],
        ]);

        $cliente->update([
            'ciudad' => $data['ciudad'] ?? null,
            'objetivo_principal' => $data['objetivo_principal'] ?? null,
            'estado_cliente' => $data['estado_cliente'],
        ]);

        if ($cliente->usuario && array_key_exists('telefono', $data)) {
            $cliente->usuario->update(['telefono' => $data['telefono']]);
        }

        return back()->with('success', 'Cliente actualizado.');
    }

    public function asignarCoach(Request $request, int $id): RedirectResponse
    {
        $cliente = Cliente::query()->findOrFail($id);

        $data = $request->validate([
            'id_coach' => ['required', 'integer', 'exists:coaches,id_coach'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        $anterior = $cliente->id_coach ?: $cliente->id_coach_asignado;

        $cliente->update([
            'id_coach' => $data['id_coach'],
            'id_coach_asignado' => $data['id_coach'],
        ]);

        HistorialAsignacionCoach::create([
            'id_cliente' => $cliente->id_cliente,
            'id_coach_anterior' => $anterior,
            'id_coach_nuevo' => $data['id_coach'],
            'id_admin' => Auth::id(),
            'motivo' => $data['motivo'] ?? 'Asignación desde Admin',
            'asignado_en' => now(),
        ]);

        return back()->with('success', 'Coach asignado al cliente.');
    }

    public function updateAcceso(Request $request, int $id, int $accesoId): RedirectResponse
    {
        $cliente = Cliente::query()->findOrFail($id);
        $acceso = AccesoClienteModulo::query()
            ->where('id_cliente', $cliente->id_cliente)
            ->where('id_acceso', $accesoId)
            ->firstOrFail();

        $data = $request->validate([
            'estado_acceso' => ['required', 'in:ACTIVO,SUSPENDIDO,VENCIDO,INACTIVO'],
            'fecha_fin' => ['nullable', 'date'],
        ]);

        $acceso->update([
            'estado_acceso' => $data['estado_acceso'],
            'fecha_fin' => $data['fecha_fin'] ?? $acceso->fecha_fin,
        ]);

        return back()->with('success', 'Acceso de módulo actualizado.');
    }

    private function listar(Request $request, string $modalidad, string $view, string $title, string $subtitle): View
    {
        $q = trim((string) $request->get('q', ''));

        $clientes = Cliente::query()
            ->with(['usuario', 'planesCliente.plan', 'rachas', 'institucion', 'horario', 'coach.usuario'])
            ->where('modalidad', $modalidad)
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('usuario', function ($inner) use ($q) {
                    $inner->where('nombres', 'like', "%{$q}%")
                        ->orWhere('apellidos', 'like', "%{$q}%")
                        ->orWhereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ["%{$q}%"]);
                });
            })
            ->orderByDesc('creado_en')
            ->paginate(20)
            ->withQueryString();

        return view("admin.clientes.{$view}", compact('clientes', 'title', 'subtitle', 'q'));
    }
}
