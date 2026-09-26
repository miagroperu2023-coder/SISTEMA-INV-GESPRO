<div class="container py-4">

    @if (session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <h2 class="h4 fw-bold mb-4">Comprobantes electrónicos</h2>

    <ul class="nav nav-tabs mb-3">
        @foreach (['pendiente' => 'Pendientes', 'aceptado' => 'Aceptados', 'rechazado' => 'Rechazados'] as $valor => $label)
            <li class="nav-item">
                <button wire:click="$set('filtro', '{{ $valor }}')"
                    class="nav-link {{ $filtro === $valor ? 'active fw-bold' : '' }}">
                    {{ $label }}
                </button>
            </li>
        @endforeach
    </ul>

    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Comprobante</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    @if ($filtro === 'rechazado')
                        <th>Motivo</th>
                    @endif
                    <th style="width:180px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($comprobantes as $voucher)
                    <tr wire:key="voucher-{{ $voucher->id }}">
                        <td>{{ $voucher->fecha->format('d/m/Y') }}</td>
                        <td>
                            {{ strtoupper($voucher->tipo_comprobante) }}
                            @if ($voucher->serie)
                                {{ $voucher->serie }}-{{ str_pad($voucher->numero, 6, '0', STR_PAD_LEFT) }}
                            @endif
                        </td>
                        <td>{{ $voucher->customer->nombre_razon_social ?? '-' }}</td>
                        <td>S/ {{ number_format($voucher->total, 2) }}</td>
                        @if ($filtro === 'rechazado')
                            <td class="small text-danger">
                                {{ $voucher->respuesta_proveedor['errors'] ?? 'Sin detalle disponible' }}
                            </td>
                        @endif
                        <td>
                            @if (in_array($voucher->estado, ['pendiente', 'rechazado']))
                                <button wire:click="reenviar({{ $voucher->id }})" wire:loading.attr="disabled"
                                    wire:target="reenviar({{ $voucher->id }})" class="btn btn-sm btn-outline-primary">
                                    <span wire:loading.remove
                                        wire:target="reenviar({{ $voucher->id }})">Reenviar</span>
                                    <span wire:loading wire:target="reenviar({{ $voucher->id }})">Enviando...</span>
                                </button>
                            @endif
                            @if ($voucher->pdf_url)
                                <a href="{{ $voucher->pdf_url }}" target="_blank"
                                    class="btn btn-sm btn-outline-secondary">PDF</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">No hay comprobantes en este estado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $comprobantes->links() }}
</div>
