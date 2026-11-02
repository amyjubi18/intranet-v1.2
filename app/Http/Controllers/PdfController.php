<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    public function generateForm()
    {
        $pdf = PDF::loadView('pdf.form');
        return $pdf->stream('formulario_editable.pdf', ['Attachment' => false]);
    }
}

