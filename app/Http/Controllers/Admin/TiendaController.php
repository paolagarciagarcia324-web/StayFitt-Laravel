<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaProducto;
use App\Models\OrdenTienda;
use App\Models\ProductoTienda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TiendaController extends Controller
{
    use \App\Support\ResolvesAdminMedia;

    public function index(): View
    {
        $categorias = $this->categorias();
        $productos = ProductoTienda::query()
            ->with('categoria')
            ->withSum('detallesOrden as unidades_vendidas', 'cantidad')
            ->orderByDesc('creado_en')
            ->paginate(10, ['*'], 'productos')
            ->withQueryString();
        $ordenes = OrdenTienda::query()->with('cliente.usuario')->orderByDesc('creado_en')->paginate(10, ['*'], 'ordenes')->withQueryString();

        return view('admin.tienda.index', compact('categorias', 'productos', 'ordenes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'sku' => ['required', 'string', 'max:40', 'unique:productos_tienda,sku'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock_total' => ['required', 'integer', 'min:0'],
            'calificacion' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'id_categoria_producto' => ['nullable', 'integer'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'imagen_url' => ['nullable', 'string', 'max:500'],
            'imagen' => ['nullable', 'image', 'max:4096'],
        ]);

        $imagenUrl = $this->resolveImagen($request, $data['imagen_url'] ?? null);

        ProductoTienda::query()->create([
            'id_categoria_producto' => ($data['id_categoria_producto'] ?? null) ?: $this->categoriaDefault()->id_categoria_producto,
            'sku' => strtoupper($data['sku']),
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => $data['precio'],
            'calificacion' => $data['calificacion'] ?? 0,
            'imagen_url' => $imagenUrl,
            'stock_total' => $data['stock_total'],
            'stock_minimo' => 2,
            'exclusivo_presencial' => true,
            'estado' => 'ACTIVO',
            'creado_en' => now(),
        ]);

        return back()->with('success', 'Producto publicado en la tienda presencial.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $producto = ProductoTienda::query()->findOrFail($id);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'sku' => ['required', 'string', 'max:40', 'unique:productos_tienda,sku,'.$producto->id_producto.',id_producto'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock_total' => ['required', 'integer', 'min:0'],
            'calificacion' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'id_categoria_producto' => ['nullable', 'integer'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'imagen_url' => ['nullable', 'string', 'max:500'],
            'imagen' => ['nullable', 'image', 'max:4096'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
        ]);

        $imagenUrl = $this->resolveImagen($request, $data['imagen_url'] ?? null, $producto->imagen_url);

        $producto->update([
            'id_categoria_producto' => ($data['id_categoria_producto'] ?? null) ?: $producto->id_categoria_producto,
            'sku' => strtoupper($data['sku']),
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => $data['precio'],
            'calificacion' => $data['calificacion'] ?? $producto->calificacion,
            'imagen_url' => $imagenUrl,
            'stock_total' => $data['stock_total'],
            'estado' => $data['estado'],
            'actualizado_en' => now(),
        ]);

        return back()->with('success', 'Producto actualizado.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $producto = ProductoTienda::query()->findOrFail($id);
        $producto->update(['estado' => 'INACTIVO', 'actualizado_en' => now()]);

        return back()->with('success', 'Producto desactivado.');
    }

    public function updateOrden(Request $request, int $id): RedirectResponse
    {
        $orden = OrdenTienda::query()->with('detalles')->findOrFail($id);

        $data = $request->validate([
            'estado_orden' => ['required', 'in:PENDIENTE,PENDIENTE_PAGO,PAGADA,PREPARADA,PREPARANDO,ENVIADA,DESPACHADA,ENTREGADA,CANCELADA'],
            'comprobante_url' => ['nullable', 'string', 'max:500'],
            'comprobante' => ['nullable', 'image', 'max:4096'],
        ]);

        $anterior = strtoupper((string) $orden->estado_orden);
        $nuevo = strtoupper((string) $data['estado_orden']);
        $pendientes = ['PENDIENTE', 'PENDIENTE_PAGO'];
        $enCurso = ['PAGADA', 'PREPARADA', 'PREPARANDO', 'ENVIADA', 'DESPACHADA', 'ENTREGADA'];

        if (in_array($anterior, $pendientes, true) && in_array($nuevo, $enCurso, true)) {
            foreach ($orden->detalles as $detalle) {
                $producto = ProductoTienda::query()->find($detalle->id_producto);
                if ($producto && (int) $producto->stock_total < (int) $detalle->cantidad) {
                    return back()->withErrors([
                        'estado_orden' => 'Stock insuficiente para '.$producto->nombre.'.',
                    ]);
                }
            }
            foreach ($orden->detalles as $detalle) {
                ProductoTienda::query()
                    ->where('id_producto', $detalle->id_producto)
                    ->where('stock_total', '>=', (int) $detalle->cantidad)
                    ->decrement('stock_total', (int) $detalle->cantidad);
            }
        } elseif (in_array($anterior, $enCurso, true) && $nuevo === 'CANCELADA') {
            foreach ($orden->detalles as $detalle) {
                ProductoTienda::query()
                    ->where('id_producto', $detalle->id_producto)
                    ->increment('stock_total', (int) $detalle->cantidad);
            }
        }

        $orden->update([
            'estado_orden' => $data['estado_orden'],
            'comprobante_url' => $this->resolveMedia($request, 'comprobante', 'comprobantes', 'comprobante_url', $orden->comprobante_url),
            'actualizado_en' => now(),
        ]);

        return back()->with('success', 'Pedido actualizado.');
    }

    private function resolveImagen(Request $request, ?string $url, ?string $fallback = null): ?string
    {
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('tienda', 'public');

            return $path;
        }

        $url = trim((string) $url);

        return $url !== '' ? $url : $fallback;
    }

    private function categorias()
    {
        $categorias = CategoriaProducto::query()->orderBy('orden')->get();

        if ($categorias->isEmpty()) {
            return collect([$this->categoriaDefault()]);
        }

        return $categorias;
    }

    private function categoriaDefault(): CategoriaProducto
    {
        return CategoriaProducto::query()->firstOrCreate(
            ['slug' => 'general'],
            [
                'nombre' => 'General',
                'descripcion' => 'Categoría base de tienda presencial',
                'orden' => 1,
                'estado' => 'ACTIVA',
            ]
        );
    }
}
