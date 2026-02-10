<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function generateSalesReport()
    {
        $sales = Sale::with(['customer', 'user', 'paymentMethod'])->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('pdf.sales-report', compact('sales'));

        return $pdf->stream('reporte_ventas.pdf');
    }
}
