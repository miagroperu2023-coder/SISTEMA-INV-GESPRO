<?php

use Livewire\Component;
use App\Models\BusinessLocation;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

new class extends Component
{
    public $mostrarForm = false;

    public $nombre;
    public $descripcion;
    public $categoria_id;
    public $variantes = [];

    public $nuevaVarianteProductoId = null;
    public $nuevaVariante = [
        'size_id' => '',
        'color' => '',
        'precio_compra' => null,
        'precio_venta' => null,
        'stock' => null,
    ];

    // buscador de inventario
    public $buscarProducto = '';

    public function abrirForm()
    {
        $this->reset(['nombre', 'descripcion', 'categoria_id', 'variantes']);
        $this->mostrarForm = true;
    }

    public function guardar()
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categories,id',
        ]);

        $product = Product::create([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'category_id' => $this->categoria_id,
        ]);

        foreach ($this->variantes as $sizeId => $data) {
            if (empty($data['color'])) continue;

            $product->variants()->create([
                'size_id' => $sizeId,
                'color' => $data['color'],
                'precio_compra' => $data['precio_compra'] ?? 0,
                'precio_venta' => $data['precio_venta'] ?? 0,
                'stock' => $data['stock'] ?? 0,
                'sku' => strtoupper(uniqid('SKU')),
            ]);
        }

        $this->mostrarForm = false;
        session()->flash('ok', 'Producto registrado con éxito.');
    }

    public function actualizarCampo($variantId, $campo, $valor)
    {
        if (!in_array($campo, ['precio_compra', 'precio_venta', 'stock', 'color'])) return;
        ProductVariant::where('id', $variantId)->update([$campo => $valor]);
    }

    public function actualizarTalla($variantId, $sizeId)
    {
        ProductVariant::where('id', $variantId)->update(['size_id' => $sizeId]);
    }

    public function eliminarVariante($variantId)
    {
        ProductVariant::where('id', $variantId)->delete();
    }

    public function abrirNuevaVariante($productId)
    {
        $this->nuevaVarianteProductoId = $this->nuevaVarianteProductoId === $productId ? null : $productId;
        $this->nuevaVariante = [
            'size_id' => '',
            'color' => '',
            'precio_compra' => null,
            'precio_venta' => null,
            'stock' => null,
        ];
        $this->resetErrorBag();
    }

    public function guardarNuevaVariante($productId)
    {
        $this->validate([
            'nuevaVariante.size_id' => 'required|exists:size_sets,id',
            'nuevaVariante.color' => 'required|string',
            'nuevaVariante.precio_compra' => 'nullable|numeric|min:0',
            'nuevaVariante.precio_venta' => 'nullable|numeric|min:0',
            'nuevaVariante.stock' => 'nullable|integer|min:0',
        ], [
            'nuevaVariante.size_id.required' => 'Selecciona una talla.',
            'nuevaVariante.color.required' => 'El color es obligatorio.',
        ]);

        $existe = ProductVariant::where('product_id', $productId)
            ->where('size_id', $this->nuevaVariante['size_id'])
            ->where('color', $this->nuevaVariante['color'])
            ->exists();

        if ($existe) {
            $this->addError('nuevaVariante.color', 'Ya existe una variante con esa talla y color para este producto.');
            return;
        }

        Product::find($productId)->variants()->create([
            'size_id' => $this->nuevaVariante['size_id'],
            'color' => $this->nuevaVariante['color'],
            'precio_compra' => $this->nuevaVariante['precio_compra'] ?? 0,
            'precio_venta' => $this->nuevaVariante['precio_venta'] ?? 0,
            'stock' => $this->nuevaVariante['stock'] ?? 0,
            'sku' => strtoupper(uniqid('SKU')),
        ]);

        $this->nuevaVarianteProductoId = null;
        session()->flash('ok', 'Variante agregada con éxito.');
    }

    public function render()
    {
        $busqueda = trim($this->buscarProducto);
        $palabras = $busqueda ? preg_split('/\s+/', $busqueda) : [];

        $query = Product::with([
            'category',
            'variants' => function ($q) use ($palabras) {
                $q->orderBy('color')->orderBy('size_id');

                // cada palabra debe coincidir con el nombre del producto, el color
                // o la talla de ESA misma variante — así solo quedan las filas exactas
                foreach ($palabras as $palabra) {
                    $q->where(function ($qq) use ($palabra) {
                        $qq->where('color', 'like', "%{$palabra}%")
                            ->orWhereHas('size', fn($s) => $s->where('valor', 'like', "%{$palabra}%"))
                            ->orWhereHas('product', fn($p) => $p->where('nombre', 'like', "%{$palabra}%"));
                    });
                }
            },
            'variants.size',
        ]);

        if ($busqueda) {
            // filtra qué productos aparecen: al menos una palabra debe calzar en nombre o en alguna variante
            foreach ($palabras as $palabra) {
                $query->where(function ($q) use ($palabra) {
                    $q->where('nombre', 'like', "%{$palabra}%")
                        ->orWhereHas('variants', function ($qq) use ($palabra) {
                            $qq->where('color', 'like', "%{$palabra}%")
                                ->orWhereHas('size', fn($s) => $s->where('valor', 'like', "%{$palabra}%"));
                        });
                });
            }
        }

        $productos = $query->orderBy('nombre')->get();

        // si hay búsqueda activa, oculta productos cuyas variantes quedaron todas filtradas (ninguna calzó)
        if ($busqueda) {
            $productos = $productos->filter(fn($p) => $p->variants->isNotEmpty());
        }

        return $this->view([
            'productos' => $productos,
            'categorias' => Category::where('estado', 'ACTIVO')->get(),
            'todasLasTallas' => BusinessLocation::find(session('sede_activa_id'))->tallasActivas(),
        ]);
    }
};
