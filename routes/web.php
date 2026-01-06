<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PerfilController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\WebController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContactoController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS (Catálogo y Carrito)
|--------------------------------------------------------------------------
*/

// Rutas de Catálogo Web
Route::get('/', [WebController::class, 'index'])->name('web.index');
Route::get('/producto/{id}', [WebController::class, 'show'])->name('web.show');
// Ruta de Contacto para la Tienda Online (Pública)
Route::get('/contacto', [ContactoController::class, 'index'])->name('contacto.index.web');

// Rutas de Carrito (Shopping Cart)
Route::get('/carrito', [CarritoController::class, 'mostrar'])->name('carrito.mostrar')->middleware('auth');
Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
Route::get('/carrito/sumar', [CarritoController::class, 'sumar'])->name('carrito.sumar');
Route::get('/carrito/restar', [CarritoController::class, 'restar'])->name('carrito.restar');
Route::get('/carrito/eliminar/{id}', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
Route::get('/carrito/vaciar', [CarritoController::class, 'vaciar'])->name('carrito.vaciar');
Route::get('/carrito/actualizar/{producto_id}/{cantidad}', [App\Http\Controllers\CarritoController::class, 'actualizar'])->name('carrito.actualizar');
Route::get('/carrito/cotizacion/pdf', [App\Http\Controllers\CarritoController::class, 'generarPdfCotizacion'])->name('carrito.pdf');


/*
|--------------------------------------------------------------------------
| RUTAS CON AUTENTICACIÓN (Dashboard, Admin, Agente)
|--------------------------------------------------------------------------
*/

// Rutas que requieren autenticación (Panel)
Route::middleware(['auth'])->group(function () {
    
    // GESTIÓN DE RECURSOS (CRUD)
    Route::resource('usuarios', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('productos', ProductoController::class);
    Route::resource('clientes', ClienteController::class); // Rutas CRUD de Clientes

    // Ruta de Contacto para el Panel (Protegida)
    Route::get('/panel/contacto', [ContactoController::class, 'index'])->name('contacto.index.panel');
    // RUTAS DE TOGGLE (Activar/Inactivar)
    Route::patch('usuarios/{usuario}/toggle', [UserController::class, 'toggleStatus'])->name('usuarios.toggle');
    Route::patch('clientes/{cliente}/toggle', [ClienteController::class, 'toggleStatus'])->name('clientes.toggle');

    // FLUJO DE PEDIDO DEL AGENTE
    Route::get('/clientes/seleccionar', [WebController::class, 'selectClient'])->name('clientes.select');
    Route::get('/pedido/iniciar/{cliente}', [WebController::class, 'startOrder'])->name('pedido.start');
    Route::get('/pedido/cancelar', function () {
        Illuminate\Support\Facades\Session::forget(['carrito', 'current_client_id', 'current_client_name']);
        return redirect()->route('web.index')->with('mensaje', 'Pedido en curso cancelado.');
    })->name('pedido.cancel_current');

    // GESTIÓN DE PEDIDOS Y PERFIL
    Route::post('/pedido/realizar', [PedidoController::class, 'realizar'])->name('pedido.realizar'); // Finalizar pedido
    Route::get('/perfil/pedidos', [PedidoController::class, 'index'])->name('perfil.pedidos'); // Listado de pedidos
    // Route::patch('/pedidos/{id}/estado', [PedidoController::class, 'cambiarEstado'])->name('pedidos.cambiar.estado'); // Cambio de estado
    Route::put('pedidos/{id}/cambiar-estado', [PedidoController::class, 'cambiarEstado'])->name('pedido.cambiarEstado');
    // Ruta para actualizar guías de envío sin tocar la BD (AJAX)
    Route::post('/pedidos/{id}/update-guia', [App\Http\Controllers\PedidoController::class, 'updateGuia'])
    ->name('pedidos.updateGuia');
    Route::get('/pedidos/{id}/pdf', [App\Http\Controllers\PedidoController::class, 'generarPdfPedido'])->name('pedidos.pdf');
    
    // RUTAS BASE
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/exportar-pedidos', [DashboardController::class, 'exportarPedidosExcel'])
    ->name('dashboard.exportar.pedidos')
    ->middleware('role:admin'); // Asegura que solo el admin pueda acceder

    Route::post('logout', function(){
        Auth::logout();
        return redirect('/login');
    })->name('logout');

    Route::get('/perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
});


/*
|--------------------------------------------------------------------------
| RUTAS DE AUTENTICACIÓN (Acceso de Invitados)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function(){
    // Login
    Route::get('login', function(){
        return view('autenticacion.login');
    })->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');

    // Registro
    Route::get('/registro', [RegisterController::class, 'showRegistroForm'])->name('registro');
    Route::post('/registro', [RegisterController::class, 'registrar'])->name('registro.store');

    // Recuperación de Contraseña
    Route::get('password/reset', [ResetPasswordController::class, 'showRequestForm'])->name('password.request');
    Route::post('password/email', [ResetPasswordController::class, 'sendResetLinkEmail'])->name('password.send-link');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'resetPassword'])->name('password.update');

});