<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Ventas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Ventas</h1>
        <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th>Método de Pago</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $sale->customer ? $sale->customer->nombre . ' ' . $sale->customer->apellido : 'N/A' }}</td>
                    <td>{{ $sale->user ? $sale->user->name : 'Sistema' }}</td>
                    <td>{{ $sale->paymentMethod ? $sale->paymentMethod->nombre : 'N/A' }}</td>
                    <td>${{ number_format($sale->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align: right;"><strong>Total General:</strong></td>
                <td><strong>${{ number_format($sales->sum('total'), 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Página <span class="page-number"></span>
    </div>
</body>
</html>
