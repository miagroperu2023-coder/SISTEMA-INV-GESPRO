{{-- resources/views/vouchers/print.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>{{ strtoupper($voucher->tipo_comprobante) }} {{ $voucher->serie }}-{{ $voucher->numero }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            width: 76mm;
            margin: 0 auto;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            color: #000;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .titulo-empresa {
            font-size: 12px;
            font-weight: bold;
            margin-top: 4px;
        }

        .separador {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th {
            text-align: left;
            font-size: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 2px;
        }

        .items-table td {
            font-size: 10px;
            padding: 2px 0;
            vertical-align: top;
        }

        .col-cant {
            width: 12%;
        }

        .col-item {
            width: 56%;
        }

        .col-pu {
            width: 16%;
            text-align: right;
        }

        .col-total {
            width: 16%;
            text-align: right;
        }

        .totales-row td {
            padding: 1px 0;
            font-size: 11px;
        }

        .totales-row .label {
            text-align: left;
        }

        .totales-row .valor {
            text-align: right;
        }

        .footer-legal {
            font-size: 9px;
            text-align: center;
            margin-top: 6px;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">

    @php
        $business = $voucher->businessLocation->business;
        $sede = $voucher->businessLocation;
    @endphp

    {{-- DATOS DEL NEGOCIO — 100% dinámico, saca todo de la BD --}}
    <div class="center">
        <div class="titulo-empresa">{{ $business->nombre_comercial }}</div>
    </div>

    <div class="center" style="margin-top:4px;">
        @if ($business->razon_social)
            {{ $business->razon_social }}<br>
        @endif
        @if ($sede->direccion)
            {{ $sede->direccion }}<br>
        @endif
        @if ($business->tipo_documento === 'ruc')
            RUC: {{ $business->numero_documento }}<br>
        @endif
        @if ($sede->telefono)
            Cel.: {{ $sede->telefono }}
        @endif
    </div>

    <div class="separador"></div>

    <div class="center bold">
        {{ match ($voucher->tipo_comprobante) {
            'boleta' => 'BOLETA ELECTRÓNICA',
            'factura' => 'FACTURA ELECTRÓNICA',
            'ticket' => 'TICKET DE VENTA',
            default => strtoupper($voucher->tipo_comprobante),
        } }}
    </div>

    @if ($voucher->serie && $voucher->numero)
        <div class="center bold">{{ $voucher->serie }}-{{ str_pad($voucher->numero, 6, '0', STR_PAD_LEFT) }}</div>
    @endif

    <div style="margin-top:4px;">
        Fecha: {{ \Carbon\Carbon::parse($voucher->fecha)->format('d/m/Y') }} &nbsp;
        Hora: {{ $voucher->created_at->format('H:i:s') }}
    </div>

    {{-- CLIENTE — solo se muestra si el comprobante lo requiere --}}
    @if ($voucher->tipo_comprobante !== 'ticket' && $voucher->customer)
        <div style="margin-top:4px;">
            <span class="bold">Cliente:</span> {{ $voucher->customer->nombre_razon_social }}<br>
            {{ $voucher->customer->tipo_documento === 'ruc' ? 'RUC' : 'DNI' }}:
            {{ $voucher->customer->numero_documento }}
            @if ($voucher->customer->direccion)
                <br>Dirección: {{ $voucher->customer->direccion }}
            @endif
        </div>
    @endif

    <div style="margin-top:2px;">Moneda: SOLES</div>

    <div class="separador"></div>

    {{-- ÍTEMS --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="col-cant">Cant.</th>
                <th class="col-item">Item</th>
                <th class="col-pu">P.U.</th>
                <th class="col-total">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($voucher->items as $item)
                <tr>
                    <td class="col-cant">{{ number_format($item->cantidad, 0) }}</td>
                    <td class="col-item">
                        {{ $item->variant->product->nombre }}
                        <br><small>{{ $item->variant->color }} — Talla
                            {{ $item->variant->size->valor }}</small>
                    </td>
                    <td class="col-pu">{{ number_format($item->precio_venta, 2) }}</td>
                    <td class="col-total">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="separador"></div>

    {{-- DESGLOSE IGV — solo boleta/factura y solo si el negocio discrimina IGV (no NRUS) --}}
    @if ($voucher->tipo_comprobante !== 'ticket' && $voucher->igv_total > 0)
        <table>
            <tr class="totales-row">
                <td class="label">OP.GRAVADAS</td>
                <td class="valor">{{ number_format($voucher->subtotal, 2) }}</td>
            </tr>
            <tr class="totales-row">
                <td class="label">IGV (18%)</td>
                <td class="valor">{{ number_format($voucher->igv_total, 2) }}</td>
            </tr>
        </table>
    @endif

    <table>
        <tr class="totales-row bold">
            <td class="label">TOTAL</td>
            <td class="valor">S/ {{ number_format($voucher->total, 2) }}</td>
        </tr>
    </table>

    <div style="margin-top:4px;">
        SON: {{ $montoEnLetras }}
    </div>

    <div class="separador"></div>

    {{-- FORMAS DE PAGO --}}
    <div class="bold">FORMAS DE PAGO</div>
    @foreach ($voucher->payments as $pago)
        <div style="display:flex; justify-content:space-between;">
            <span>{{ strtoupper($pago->metodo_pago) }}</span>
            <span>S/ {{ number_format($pago->monto, 2) }}</span>
        </div>
    @endforeach

    <div class="separador"></div>
    <div class="center">¡Gracias por su compra!</div>

    {{-- Texto legal y QR solo para boleta/factura (comprobante fiscal real) --}}
    @if ($voucher->tipo_comprobante !== 'ticket')
        <div class="footer-legal">
            Representación impresa de {{ $voucher->tipo_comprobante === 'factura' ? 'FACTURA' : 'BOLETA' }}
            ELECTRÓNICA. Puede verificarla en el portal de SUNAT.
        </div>

        {{--
            El QR real se genera cuando conectes SUNAT (NubeFact u otro PSE
            te devuelve el string del QR ya codificado). Por ahora, mientras
            no esté conectado, se omite.
        --}}
        {{-- <img src="{{ $voucher->qr_path }}" style="width:90px; display:block; margin:8px auto;"> --}}
    @endif

</body>

</html>
