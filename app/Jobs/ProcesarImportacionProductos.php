<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcesarImportacionProductos implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $importId,
        public array $filas,
        public int $businessLocationId,
    ) {
        //
    }

    public function handle(): void
    {
        Log::info("importacion-productos → inicio (import_id={$this->importId}, filas=" . count($this->filas) . ")");

        $import = ProductImport::find($this->importId);
        if (!$import) {
            Log::error("importacion-productos → no se encontró el ProductImport id={$this->importId}, se aborta.");
            return;
        }

        $import->update(['estado' => 'procesando']);
        $creados = 0;
        $errores = [];

        foreach ($this->filas as $index => $fila) {
            try {
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
                Log::info("importacion-productos → fila " . ($index + 1) . " OK: '{$fila['producto']}' — {$fila['color']}");
            } catch (\Throwable $e) {
                $mensaje = "fila " . ($index + 1) . " ('{$fila['producto']}'): {$e->getMessage()}";
                $errores[] = $mensaje;
                Log::error("importacion-productos → ERROR en {$mensaje}");
            }
        }

        $import->update(['estado' => 'completado', 'creados' => $creados]);

        Log::info("importacion-productos → fin (import_id={$this->importId}, creados={$creados} de " . count($this->filas) . ", errores=" . count($errores) . ")");
    }
}
