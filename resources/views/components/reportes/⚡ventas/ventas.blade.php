<div class="container py-4">

    <h2 class="h4 fw-bold mb-4">Reporte de ventas</h2>

    <div class="row g-2 mb-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small">Fecha inicial</label>
            <input type="date" wire:model.live="fecha_inicio" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
            <label class="form-label small">Fecha final</label>
            <input type="date" wire:model.live="fecha_fin" class="form-control form-control-sm">
        </div>
        <div class="col-md-6 text-md-end">
            <button wire:click="exportarExcel" class="btn btn-success btn-sm">📊 Exportar Excel</button>
            <button wire:click="exportarPdf" class="btn btn-danger btn-sm">📄 Exportar PDF</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Comprobante</th>
                    <th>Producto</th>
                    <th>Talla</th>
                    <th>Color</th>
                    <th>Cant.</th>
                    <th>P. Venta</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($filas as $fila)
                    <tr>
                        <td>{{ $fila->fecha->format('d/m/Y') }}</td>
                        <td>{{ $fila->comprobante }}</td>
                        <td>{{ $fila->producto }}</td>
                        <td>{{ $fila->talla }}</td>
                        <td>{{ $fila->color }}</td>
                        <td>{{ $fila->cantidad }}</td>
                        <td>S/ {{ number_format($fila->precio_venta, 2) }}</td>
                        <td>S/ {{ number_format($fila->subtotal, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No hay ventas en ese rango de fechas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="h6 fw-bold mb-2">Totales por método de pago</h3>
                    @forelse ($totalesPorMetodo as $metodo => $monto)
                        <div class="d-flex justify-content-between">
                            <span class="text-capitalize">{{ $metodo }}</span>
                            <span>S/ {{ number_format($monto, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Sin pagos registrados.</p>
                    @endforelse
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total</span>
                        <span>S/ {{ number_format($filas->sum('subtotal'), 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h3 class="h6 fw-bold mb-2">Resumen de ganancia</h3>
                    <div class="d-flex justify-content-between">
                        <span>Total vendido</span>
                        <span>S/ {{ number_format($resumenGanancia['totalVentas'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted">
                        <span>Costo de mercadería</span>
                        <span>- S/ {{ number_format($resumenGanancia['totalCosto'], 2) }}</span>
                    </div>
                    <hr>
                    <div
                        class="d-flex justify-content-between fw-bold {{ $resumenGanancia['ganancia'] >= 0 ? 'text-success' : 'text-danger' }}">
                        <span>Ganancia</span>
                        <span>S/ {{ number_format($resumenGanancia['ganancia'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
