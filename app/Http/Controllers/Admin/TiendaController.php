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
    public function index(): View
    {
        $categorias = $this->categorias();
        $productos = ProductoTienda::query()->with('categoria')->orderByDesc('creado_en')->paginate(12, ['*'], 'productos');
        $ordenes = OrdenTienda::query()->with('cliente.usuario')->orderByDesc('creado_en')->paginate(12, ['*'], 'ordenes');

        return view('admin.tienda.index', compact('categorias', 'productos', 'ordenes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'sku' => ['required', 'string', 'max:40', 'unique:productos_tienda,sku'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock_total' => ['required', 'integer', 'min:0'],
            'id_categoria_producto' => ['nullable', 'integer'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ]);

        ProductoTienda::query()->create([
            'id_categoria_producto' => $data['id_categoria_producto'] ?: $this->categoriaDefault()->id_categoria_producto,
            'sku' => strtoupper($data['sku']),
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'precio' => $data['precio'],
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
            'precio' => ['required', 'numeric', 'min:0'],
            'stock_total' => ['required', 'integer', 'min:0'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
        ]);

        $producto->update($data + ['actualizado_en' => now()]);

        return back()->with('success', 'Producto actualizado.');
    }

    public function updateOrden(Request $request, int $id): RedirectResponse
    {
        $orden = OrdenTienda::query()->findOrFail($id);

        $data = $request->validate([
            'estado_orden' => ['required', 'in:PENDIENTE_PAGO,PAGADA,PREPARADA,ENVIADA,ENTREGADA,CANCELADA'],
        ]);

        $orden->update($data + ['actualizado_en' => now()]);

        return back()->with('success', 'Pedido actualizado.');
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
