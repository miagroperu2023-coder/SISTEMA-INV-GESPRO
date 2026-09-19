<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: left;
        }

        th {
            background: #f0f0f0;
        }

        .totales {
            margin-top: 20px;
        }

        .totales td {
            border: none;
            padding: 3px 10px;
        }
    </style>
</head>

<body>
    <h2>Reporte de ventas</h2>
    <p>Del {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al
        {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>

    <table>
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
            @foreach ($filas as $fila)
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
            @endforeach
        </tbody>
    </table>

    <table class="totales">
        <tr>
            <td><strong>Total general:</strong></td>
            <td>S/ {{ number_format($totalGeneral, 2) }}</td>
        </tr>
        @foreach ($totalesPorMetodo as $metodo => $monto)
            <tr>
                <td>{{ ucfirst($metodo) }}:</td>
                <td>S/ {{ number_format($monto, 2) }}</td>
            </tr>
        @endforeach
    </table>
</body>

</html>
