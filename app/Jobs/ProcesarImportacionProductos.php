<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcesarImportacionProductos implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $importId,
        public array $filas,
        public int $businessLocationId,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $import = ProductImport::find($this->importId);
        if (!$import) return;

        $import->update(['estado' => 'procesando']);
        $creados = 0;

        foreach ($this->filas as $fila) {
            $categoria = Category::firstOrCreate([
                'business_location_id' => $this->businessLocationId,
                'nombre' => $fila['categoria'],
            ]);

            $product = Product::firstOrCreate([
                'business_location_id' => $this->businessLocationId,
                'nombre' => $fila['producto'],
                'category_id' => $categoria->id,
            ]);

            $product->variants()->create([
                'size_id' => $fila['size_id'],
                'color' => $fila['color'],
                'precio_compra' => $fila['precio_compra'],
                'precio_venta' => $fila['precio_venta'],
                'stock' => $fila['stock'],
                'sku' => strtoupper(uniqid('SKU')),
            ]);

            $creados++;
        }

        $import->update(['estado' => 'completado', 'creados' => $creados]);
    }
}
