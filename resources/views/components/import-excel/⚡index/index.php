<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Category;
use App\Models\Product;
use App\Models\BusinessLocation;
use App\Models\ProductImport;
use App\Jobs\ProcesarImportacionProductos;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component
{
    use WithFileUploads;

    public $archivo;
    public $paso = 'subir';
    public $filasAnalizadas = [];
    public $importActualId = null;

    public function analizar()
    {
        $this->validate(['archivo' => 'required|mimes:xlsx,xls|max:5120']);

        $filas = Excel::toCollection(null, $this->archivo)->first();
        $sedeId = session('sede_activa_id');
        $sede = BusinessLocation::find($sedeId);
        $tallasActivas = $sede->tallasActivas()->flatten();

        $productosExistentes = Product::where('business_location_id', $sedeId)
            ->with('variants')
            ->get()
            ->keyBy(fn($p) => mb_strtolower($p->nombre));

        $vistasEnEsteArchivo = [];
        $analizadas = [];

        foreach ($filas->skip(1) as $index => $fila) {
            $numeroFila = $index + 2;

            $nombreProducto = trim($fila[0] ?? '');
            $categoriaNombre = trim($fila[1] ?? '');
            $color = trim($fila[2] ?? '');
            $tallaValor = trim($fila[3] ?? '');
            $precioCompra = $fila[4] ?? 0;
            $precioVenta = $fila[5] ?? 0;
            $stock = $fila[6] ?? 0;

            if (empty($nombreProducto) && empty($categoriaNombre) && empty($color) && empty($tallaValor)) {
                continue;
            }

            $fila_data = [
                'numero_fila' => $numeroFila,
                'producto' => $nombreProducto,
                'categoria' => $categoriaNombre,
                'color' => $color,
                'talla' => $tallaValor,
                'precio_compra' => $precioCompra,
                'precio_venta' => $precioVenta,
                'stock' => $stock,
                'size_id' => null,
                'estado' => 'ok',
                'mensaje' => 'Se creará',
            ];

            if (empty($nombreProducto) || empty($categoriaNombre) || empty($color) || empty($tallaValor)) {
                $fila_data['estado'] = 'error';
                $fila_data['mensaje'] = 'Faltan datos obligatorios.';
                $analizadas[] = $fila_data;
                continue;
            }

            if (!is_numeric($precioCompra) || !is_numeric($precioVenta) || !is_numeric($stock)) {
                $fila_data['estado'] = 'error';
                $fila_data['mensaje'] = 'Precio o stock no es un número válido.';
                $analizadas[] = $fila_data;
                continue;
            }

            $size = $tallasActivas->first(fn($s) => mb_strtolower(trim($s->valor)) === mb_strtolower($tallaValor));

            if (!$size) {
                $fila_data['estado'] = 'error';
                $fila_data['mensaje'] = "La talla '{$tallaValor}' no está activada en esta sede.";
                $analizadas[] = $fila_data;
                continue;
            }
            $fila_data['size_id'] = $size->id;

            $clave = mb_strtolower($nombreProducto . '|' . $color . '|' . $tallaValor);
            if (isset($vistasEnEsteArchivo[$clave])) {
                $fila_data['estado'] = 'duplicado_archivo';
                $fila_data['mensaje'] = "Repetida en la fila {$vistasEnEsteArchivo[$clave]}.";
                $analizadas[] = $fila_data;
                continue;
            }
            $vistasEnEsteArchivo[$clave] = $numeroFila;

            $productoExistente = $productosExistentes->get(mb_strtolower($nombreProducto));
            if ($productoExistente) {
                $existeVariante = $productoExistente->variants->contains(
                    fn($v) => $v->size_id === $size->id && mb_strtolower($v->color) === mb_strtolower($color)
                );
                if ($existeVariante) {
                    $fila_data['estado'] = 'ya_existe';
                    $fila_data['mensaje'] = 'Ya existe esta variante, se omitirá.';
                    $analizadas[] = $fila_data;
                    continue;
                }
            }

            $analizadas[] = $fila_data;
        }

        $this->filasAnalizadas = $analizadas;
        $this->paso = 'previsualizar';
    }

    public function confirmarImportacion()
    {
        $sedeId = session('sede_activa_id');
        $filasParaCrear = collect($this->filasAnalizadas)->where('estado', 'ok')->values()->toArray();

        $import = ProductImport::create([
            'business_location_id' => $sedeId,
            'estado' => 'pendiente',
            'total_filas' => count($filasParaCrear),
        ]);

        ProcesarImportacionProductos::dispatch($import->id, $filasParaCrear, $sedeId);

        $this->importActualId = $import->id;
        $this->paso = 'procesando';
    }

    public function refrescarEstado()
    {
        $import = ProductImport::find($this->importActualId);

        if ($import && $import->estado === 'completado') {
            session()->flash('ok', "Importación completa: se crearon {$import->creados} de {$import->total_filas} producto(s).");
            $this->reset(['archivo', 'filasAnalizadas', 'importActualId']);
            $this->paso = 'subir';
        }
    }

    public function cancelar()
    {
        $this->reset(['archivo', 'filasAnalizadas', 'importActualId']);
        $this->paso = 'subir';
    }

    public function render()
    {
        return $this->view();
    }
};
