<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController; // Importar la clase base de Laravel

// Tu clase ahora extiende la clase base del framework
abstract class Controller extends BaseController 
{
    // Incluir los traits necesarios para toda la funcionalidad del controlador
    use AuthorizesRequests, ValidatesRequests; 
}