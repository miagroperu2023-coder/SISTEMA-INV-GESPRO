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
        return $this->view([
            'productos' => Product::with([
                'category',
                'variants' => fn($q) => $q->orderBy('color')->orderBy('size_id'),
                'variants.size',
            ])
                ->when($this->buscarProducto, fn($q) => $q->where('nombre', 'like', '%' . $this->buscarProducto . '%'))
                ->orderBy('nombre')
                ->get(),
            'categorias' => Category::where('estado', 'ACTIVO')->get(),
            'todasLasTallas' => BusinessLocation::find(session('sede_activa_id'))->tallasActivas(),
        ]);
    }
};
