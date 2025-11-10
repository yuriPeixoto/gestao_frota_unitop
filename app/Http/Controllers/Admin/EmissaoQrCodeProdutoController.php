<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmissaoQrCodeProdutoController extends Controller
{
    public function index()
    {
        return view('admin.emissaoqrcode.index');
    }
}
