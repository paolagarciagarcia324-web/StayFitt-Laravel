<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\OrdenDetalle;
use App\Models\OrdenTienda;
use App\Models\ProductoTienda;
use App\Support\ResolvesCliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TiendaController extends Controller
{
    use ResolvesCliente;

    public function index(): View
    {
        $cliente = $this->resolveClienteOrAbort();
        $productos = ProductoTienda::query()
            ->with('categoria')
            ->where(function ($q) {
                $q->where('estado', 'ACTIVO')->orWhereNull('estado');
            })
            ->orderBy('nombre')
            ->paginate(12);

        $carrito = $this->carritoActivo($cliente->id_cliente);
        $itemsCount = $carrito?->items()->sum('cantidad') ?? 0;

        return view('cliente.tienda.index', compact('cliente', 'productos', 'itemsCount'));
    }

    public function carrito(): View
    {
        $cliente = $this->resolveClienteOrAbort();
        $carrito = $this->carritoActivo($cliente->id_cliente);
        $items = $carrito
            ? $carrito->items()->with('producto')->get()
            : collect();
        $subtotal = $items->sum(fn ($i) => (float) $i->precio_unitario * (int) $i->cantidad);

        return view('cliente.tienda.carrito', compact('cliente', 'carrito', 'items', 'subtotal'));
    }

    public function agregar(Request $request, int $id): RedirectResponse
    {
        $cliente = $this->resolveClienteOrAbort();
        $producto = ProductoTienda::query()->findOrFail($id);

        $data = $request->validate([
            'cantidad' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);
        $cantidad = (int) ($data['cantidad'] ?? 1);

        $carrito = $this->carritoActivo($cliente->id_cliente, true);
        $item = CarritoItem::query()
            ->where('id_carrito', $carrito->id_carrito)
            ->where('id_producto', $producto->id_producto)
            ->first();

        if ($item) {
            $item->update(['cantidad' => $item->cantidad + $cantidad]);
        } else {
            CarritoItem::query()->create([
                'id_carrito' => $carrito->id_carrito,
                'id_producto' => $producto->id_producto,
                'cantidad' => $cantidad,
                'precio_unitario' => $producto->precio,
            ]);
        }

        return back()->with('success', 'Producto agregado al carrito.');
    }

    public function actualizarItem(Request $request, int $id): RedirectResponse
    {
        $cliente = $this->resolveClienteOrAbort();
        $carrito = $this->carritoActivo($cliente->id_cliente);
        if (! $carrito) {
            return back()->with('error', 'No hay carrito activo.');
        }

        $data = $request->validate([
            'cantidad' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        $item = CarritoItem::query()
            ->where('id_carrito', $carrito->id_carrito)
            ->where('id_item', $id)
            ->firstOrFail();

        if ((int) $data['cantidad'] === 0) {
            $item->delete();
        } else {
            $item->update(['cantidad' => $data['cantidad']]);
        }

        return back()->with('success', 'Carrito actualizado.');
    }

    public function checkout(Request $request): RedirectResponse
    {
        $cliente = $this->resolveClienteOrAbort();
        $carrito = $this->carritoActivo($cliente->id_cliente);
        if (! $carrito) {
            return back()->with('error', 'Tu carrito está vacío.');
        }

        $items = $carrito->items()->with('producto')->get();
        if ($items->isEmpty()) {
            return back()->with('error', 'Tu carrito está vacío.');
        }

        $data = $request->validate([
            'tipo_entrega' => ['required', 'in:RETIRO,DOMICILIO'],
            'direccion_entrega' => ['nullable', 'string', 'max:255'],
            'ciudad_entrega' => ['nullable', 'string', 'max:80'],
            'observacion' => ['nullable', 'string', 'max:400'],
        ]);

        if ($data['tipo_entrega'] === 'DOMICILIO' && empty($data['direccion_entrega'])) {
            return back()->withErrors(['direccion_entrega' => 'Indica la dirección de entrega.'])->withInput();
        }

        $subtotal = $items->sum(fn ($i) => (float) $i->precio_unitario * (int) $i->cantidad);

        DB::transaction(function () use ($cliente, $carrito, $items, $subtotal, $data) {
            $orden = OrdenTienda::query()->create([
                'numero_orden' => 'ORD-'.now()->format('ymd').'-'.$cliente->id_cliente.'-'.random_int(100, 999),
                'id_cliente' => $cliente->id_cliente,
                'subtotal' => $subtotal,
                'costo_envio' => 0,
                'total' => $subtotal,
                'tipo_entrega' => $data['tipo_entrega'],
                'direccion_entrega' => $data['direccion_entrega'] ?? null,
                'ciudad_entrega' => $data['ciudad_entrega'] ?? null,
                'estado_orden' => 'PENDIENTE',
                'observacion_admin' => $data['observacion'] ?? null,
            ]);

            foreach ($items as $item) {
                OrdenDetalle::query()->create([
                    'id_orden' => $orden->id_orden,
                    'id_producto' => $item->id_producto,
                    'id_variante' => $item->id_variante,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'subtotal' => (float) $item->precio_unitario * (int) $item->cantidad,
                ]);
            }

            $carrito->items()->delete();
            $carrito->update(['estado' => 'CERRADO']);
        });

        return redirect()->route('cliente.tienda.ordenes')->with('success', 'Pedido enviado. El administrador lo gestionará.');
    }

    public function ordenes(): View
    {
        $cliente = $this->resolveClienteOrAbort();
        $ordenes = OrdenTienda::query()
            ->with('detalles.producto')
            ->where('id_cliente', $cliente->id_cliente)
            ->orderByDesc('id_orden')
            ->paginate(10);

        return view('cliente.tienda.ordenes', compact('cliente', 'ordenes'));
    }

    private function carritoActivo(int $idCliente, bool $crear = false): ?Carrito
    {
        $carrito = Carrito::query()
            ->where('id_cliente', $idCliente)
            ->where('estado', 'ACTIVO')
            ->first();

        if (! $carrito && $crear) {
            $carrito = Carrito::query()->create([
                'id_cliente' => $idCliente,
                'estado' => 'ACTIVO',
            ]);
        }

        return $carrito;
    }
}
