<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt</title>
    <style>
        /* you can chnage this to apply different page size this was for a 80mm printer */
        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #000;
            margin: 0;
            padding: 0;
            width: 80mm;
        }

        html, body {
            width: 80mm;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .wrapper {
            padding: 5px;
        }

        .header,
        .footer {
            text-align: center;
        }

        .logo {
            max-height: 40px;
            margin-bottom: 5px;
        }

        .app-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .receipt-title {
            font-size: 11px;
            font-weight: bold;
            margin: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            padding-right: 5px;
        }

        th,
        td {
            padding: 3px;
            border-bottom: 1px dashed #aaa;
            text-align: left;
        }

        th {
            font-weight: bold;
        }

        .summary {
            margin-top: 10px;
            margin-right: 20px;
            text-align: right;
            font-weight: bold;
        }

        .footer {
            font-size: 9px;
            color: #444;
            margin-top: 10px;
            line-height: 1.4;
        }

        hr {
            border: none;
            border-top: 1px dashed #aaa;
            margin: 8px 0;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                width: 80mm;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        @foreach ($records as $record)
            <div class="header">
                @if(asset('img/logo3.png'))
                    <img src="{{ asset('img/logo3.png') }}" alt="Logo" class="logo">
                @else
                    <div class="app-name">{{ config('app.name') }}</div>
                @endif

                <div class="receipt-title">Receipt #{{ $record->id }}</div>
                <div>{{ $record->created_at->format('d M Y H:i') }}</div>
            </div>

            <div style="margin-top: 5px;">
                <strong>Customer:</strong> {{ $record->customer->name ?? 'Walk-in' }}<br>
                <strong>Email:</strong> {{ $record->customer->email ?? 'N/A' }}<br>
                <strong>Payment Method:</strong> {{ $record->paymentMethod->name ?? 'N/A' }}
            </div>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($record->saleItems as $index => $line)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($line->item->name, 10) }}</td>
                            <td>{{ $line->quantity }}</td>
                            <td>{{ 'TZS' }} {{ number_format($line->price, 2) }}</td>
                            <td>{{ 'TZS' }} {{ number_format($line->quantity * $line->price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="summary">
                Discount(%): {{ number_format($record->discount, 2) }}<br>
                Total: {{ 'TZS' }} {{ number_format($record->amount_paid, 2) }}
            </div>

            <hr>
        @endforeach

    </div>
    
</body>
</html>