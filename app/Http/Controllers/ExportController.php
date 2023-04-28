<?php

namespace App\Http\Controllers;
use App\Exports\BienesExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function index(){
    return view('export');
    }
    public function export(){
        return Excel::download(new BienesExport, 'bienes.xlsx');
    }
    
}
