<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class ContactoController extends Controller
{
    /**
     * Muestra la página de contacto.
     * Usa la misma vista pero determina el layout a extender.
     */
    public function index()
    {
        // Simplemente devolvemos la vista. La vista se encarga del layout.
        return view('contacto'); 
    }
}