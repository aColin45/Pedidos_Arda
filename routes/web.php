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
use App\Http\Controllers\EncuestaController;
use App\Http\Controllers\ProspectoController;
use App\Http\Controllers\VisitaController;
use App\Http\Controllers\SolicitudDesarrolloController;
use App\Http\Controllers\InventarioController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS (Catálogo y Carrito)
|--------------------------------------------------------------------------
*/

Route::get('/', [WebController::class, 'index'])->name('web.index');
Route::get('/producto/{id}', [WebController::class, 'show'])->name('web.show');
Route::get('/contacto', [ContactoController::class, 'index'])->name('contacto.index.web');

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

Route::middleware(['auth'])->group(function () {
    
    // =================================================
    // DASHBOARD Y PERFIL (Acceso Básico)
    // =================================================
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/exportar-pedidos', [DashboardController::class, 'exportarPedidosExcel'])
        ->name('dashboard.exportar.pedidos')->middleware('role:admin|superadmin'); 

    Route::post('logout', function(){
        Auth::logout();
        return redirect('/login');
    })->name('logout');
    
    Route::get('/panel/contacto', [ContactoController::class, 'index'])->name('contacto.index.panel');
    Route::get('/perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
    
    // =================================================
    // GESTIÓN DE RECURSOS CLÁSICOS
    // =================================================
    Route::resource('usuarios', UserController::class);
    Route::patch('usuarios/{usuario}/toggle', [UserController::class, 'toggleStatus'])->name('usuarios.toggle');
    Route::resource('roles', RoleController::class);
    
    Route::get('productos/exportar', [ProductoController::class, 'exportar'])->name('productos.exportar');
    Route::resource('productos', ProductoController::class);
    
    Route::get('clientes/exportar', [ClienteController::class, 'exportar'])->name('clientes.exportar');
    Route::resource('clientes', ClienteController::class); 
    Route::patch('clientes/{cliente}/toggle', [ClienteController::class, 'toggleStatus'])->name('clientes.toggle');

    // =================================================
    // PEDIDOS
    // =================================================
    Route::middleware(['can:pedido-list'])->group(function () {
        Route::get('/perfil/pedidos', [PedidoController::class, 'index'])->name('perfil.pedidos');
        Route::get('/pedidos/{id}/pdf', [PedidoController::class, 'generarPdfPedido'])->name('pedidos.pdf');
        
        // Solución: Movimos estas rutas aquí. 
        // Antes pedían 'pedido-edit' (el cual no existe en tu BD).
        Route::put('pedidos/{id}/cambiar-estado', [PedidoController::class, 'cambiarEstado'])->name('pedido.cambiarEstado');
        Route::post('/pedidos/{id}/update-guia', [PedidoController::class, 'updateGuia'])->name('pedidos.updateGuia');
        Route::get('/pedidos/{id}/editar', [PedidoController::class, 'editar'])->name('pedidos.editar');
    });

    Route::middleware(['can:pedido-create-cliente'])->group(function () {
        Route::get('/clientes/seleccionar', [WebController::class, 'selectClient'])->name('clientes.select');
        Route::get('/pedido/iniciar/{cliente}', [WebController::class, 'startOrder'])->name('pedido.start');
        Route::post('/pedido/realizar', [PedidoController::class, 'realizar'])->name('pedido.realizar');
        Route::get('/pedido/cancelar', function () {
            Illuminate\Support\Facades\Session::forget(['carrito', 'current_client_id', 'current_client_name']);
            return redirect()->route('web.index')->with('mensaje', 'Pedido en curso cancelado.');
        })->name('pedido.cancel_current');
    });

    // =================================================
    // DEVOLUCIONES
    // =================================================
    Route::middleware(['can:devolucion-create'])->group(function () {
        Route::get('/devoluciones/crear', [App\Http\Controllers\DevolucionController::class, 'create'])->name('devoluciones.create');
        Route::post('/devoluciones', [App\Http\Controllers\DevolucionController::class, 'store'])->name('devoluciones.store');
        Route::get('/api/producto-info/{codigo}', [App\Http\Controllers\DevolucionController::class, 'getProductInfo'])->name('api.producto.info');
    });

    Route::middleware(['can:devolucion-list'])->group(function () {
        Route::get('/devoluciones', [App\Http\Controllers\DevolucionController::class, 'index'])->name('devoluciones.index');
        Route::get('/devoluciones/{id}', [App\Http\Controllers\DevolucionController::class, 'show'])->name('devoluciones.show');
        Route::get('/devoluciones/{id}/pdf', [App\Http\Controllers\DevolucionController::class, 'generarPdf'])->name('devoluciones.pdf');
    });

    Route::put('/devoluciones/{id}/estado', [App\Http\Controllers\DevolucionController::class, 'updateStatus'])
        ->name('devoluciones.updateStatus')->middleware('can:devolucion-edit');

    // =================================================
    // COTIZACIONES
    // =================================================
    Route::middleware(['can:cotizacion-edit'])->group(function () {
        Route::get('/cotizaciones/{id}/editar', [App\Http\Controllers\CotizacionController::class, 'editar'])->name('cotizaciones.editar');
        Route::post('/cotizaciones/{id}/convertir', [App\Http\Controllers\CotizacionController::class, 'convertir'])->name('cotizaciones.convertir');
        Route::post('/cotizaciones/{id}/cancelar', [App\Http\Controllers\CotizacionController::class, 'cancelar'])->name('cotizaciones.cancelar');
        Route::post('/cotizaciones/{id}/rechazar', [App\Http\Controllers\CotizacionController::class, 'rechazar'])->name('cotizaciones.rechazar');
    });

    Route::middleware(['can:cotizacion-list'])->group(function () {
        Route::get('/cotizaciones', [App\Http\Controllers\CotizacionController::class, 'index'])->name('cotizaciones.index');
        Route::get('/cotizaciones/{id}/pdf', [App\Http\Controllers\PedidoController::class, 'generarPdfPedido'])->name('cotizaciones.pdf');
    });

    // =================================================
    // ALTAS DE CLIENTES
    // =================================================
    Route::middleware(['can:alta-create'])->group(function () {
        Route::get('/altas/nueva', [App\Http\Controllers\AltaClienteController::class, 'create'])->name('altas.create');
        Route::post('/altas/guardar', [App\Http\Controllers\AltaClienteController::class, 'store'])->name('altas.store');
        Route::post('/altas/{id}/documento', [App\Http\Controllers\AltaClienteController::class, 'uploadDocumento'])->name('altas.upload');
        Route::post('/altas/{id}/enviar', [App\Http\Controllers\AltaClienteController::class, 'enviarRevision'])->name('altas.enviar');
    });

    Route::middleware(['can:alta-evaluate'])->group(function () {
        Route::post('/altas/{alta_id}/documentos/{doc_id}/evaluar', [App\Http\Controllers\AltaClienteController::class, 'evaluarDocumento'])->name('altas.evaluar_doc');
        Route::post('/altas/{id}/convertir', [App\Http\Controllers\AltaClienteController::class, 'convertirCliente'])->name('altas.convertir');
        Route::post('/altas/{id}/cancelar', [App\Http\Controllers\AltaClienteController::class, 'cancelarAlta'])->name('altas.cancelar');
        Route::post('/altas/{alta_id}/referencias/{ref_id}/evaluar', [App\Http\Controllers\AltaClienteController::class, 'evaluarReferencia'])->name('altas.evaluar_ref');
        Route::put('/altas/{alta_id}/referencias/{ref_id}/corregir', [App\Http\Controllers\AltaClienteController::class, 'corregirReferencia'])->name('altas.corregir_ref');
    });

    Route::middleware(['can:alta-list'])->group(function () {
        Route::get('/altas/formato-blanco', [App\Http\Controllers\AltaClienteController::class, 'pdfEnBlanco'])->name('altas.pdf_blanco');
        Route::get('/altas', [App\Http\Controllers\AltaClienteController::class, 'index'])->name('altas.index');
        Route::get('/altas/{id}', [App\Http\Controllers\AltaClienteController::class, 'show'])->name('altas.show');
        Route::get('/altas/{id}/pdf', [App\Http\Controllers\AltaClienteController::class, 'pdf'])->name('altas.pdf');
    });

    // =================================================
    // PROSPECTOS
    // =================================================
    Route::middleware(['can:prospecto-create'])->group(function () {
        Route::resource('prospectos', ProspectoController::class)->only(['create', 'store']);
    });

    Route::middleware(['can:prospecto-edit'])->group(function () {
        Route::resource('prospectos', ProspectoController::class)->only(['edit', 'update']);
        Route::put('prospectos/{id}/estado', [ProspectoController::class, 'cambiarEstado'])->name('prospectos.cambiar_estado');
        Route::post('prospectos/{id}/convertir', [ProspectoController::class, 'convertirACliente'])->name('prospectos.convertir_cliente');
    });
    
    Route::resource('prospectos', ProspectoController::class)->only(['destroy'])->middleware('can:prospecto-delete');

    Route::middleware(['can:prospecto-list'])->group(function () {
        Route::get('prospectos-exportar', [ProspectoController::class, 'exportar'])->name('prospectos.exportar');
        Route::get('prospectos/{id}/pdf', [ProspectoController::class, 'exportarFichaPdf'])->name('prospectos.pdf');
        Route::resource('prospectos', ProspectoController::class)->only(['index', 'show']);
    });

    // =================================================
    // VISITAS
    // =================================================
    Route::middleware(['can:visita-create'])->group(function () {
        Route::resource('visitas', VisitaController::class)->only(['create', 'store']);
    });

    Route::resource('visitas', VisitaController::class)->only(['edit', 'update'])->middleware('can:visita-edit');
    Route::resource('visitas', VisitaController::class)->only(['destroy'])->middleware('can:visita-delete');

    Route::middleware(['can:visita-list'])->group(function () {
        Route::get('visitas-exportar', [VisitaController::class, 'exportar'])->name('visitas.exportar');
        Route::get('visitas/{id}/pdf', [VisitaController::class, 'exportarFichaPdf'])->name('visitas.pdf');
        Route::resource('visitas', VisitaController::class)->only(['index', 'show']);
    });

    // =================================================
    // DESARROLLOS
    // =================================================
    Route::resource('desarrollos', SolicitudDesarrolloController::class)->only(['create', 'store'])->middleware('can:desarrollo-create');
    
    Route::middleware(['can:desarrollo-edit'])->group(function () {
        Route::resource('desarrollos', SolicitudDesarrolloController::class)->only(['edit', 'update']);
        Route::put('desarrollos/{id}/estado', [SolicitudDesarrolloController::class, 'cambiarEstado'])->name('desarrollos.cambiar_estado');
    });

    Route::resource('desarrollos', SolicitudDesarrolloController::class)->only(['destroy'])->middleware('can:desarrollo-delete');

    Route::middleware(['can:desarrollo-list'])->group(function () {
        Route::get('desarrollos-exportar', [SolicitudDesarrolloController::class, 'exportar'])->name('desarrollos.exportar');
        Route::get('desarrollos/{id}/pdf', [SolicitudDesarrolloController::class, 'exportarFichaPdf'])->name('desarrollos.pdf');
        Route::resource('desarrollos', SolicitudDesarrolloController::class)->only(['index', 'show']);
    });

    // =================================================
    // INVENTARIOS
    // =================================================
    // 1. Rutas para configuración de restricciones (NUEVAS)
    Route::post('/inventarios/restricciones', [InventarioController::class, 'agregarRestriccion'])->name('inventarios.restricciones.store')->middleware('can:inventario-config');
    Route::delete('/inventarios/restricciones/{id}', [InventarioController::class, 'eliminarRestriccion'])->name('inventarios.restricciones.destroy')->middleware('can:inventario-config');

    // 2. Rutas operativas
    Route::post('/inventarios/importar', [InventarioController::class, 'importar'])->name('inventarios.importar')->middleware('can:inventario-import');
    Route::post('/inventarios/vaciar', [InventarioController::class, 'vaciar'])->name('inventarios.vaciar')->middleware('can:inventario-import');
    Route::post('/inventarios/manual', [InventarioController::class, 'actualizarManual'])->name('inventarios.manual')->middleware('can:inventario-edit');
    Route::get('/inventarios', [InventarioController::class, 'index'])->name('inventarios.index')->middleware('can:inventario-list');

    // =================================================
    // RUTA TEMPORAL PARA LIMPIAR CACHÉ DE PERMISOS
    // =================================================
    Route::get('/limpiar-cache-permisos', function() {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        return 'Caché de permisos limpiada. Ya puedes editar roles.';
    });

});

/*
|--------------------------------------------------------------------------
| RUTAS DE AUTENTICACIÓN (Acceso de Invitados)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function(){
    Route::get('login', function(){ return view('autenticacion.login'); })->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/registro', [RegisterController::class, 'showRegistroForm'])->name('registro');
    Route::post('/registro', [RegisterController::class, 'registrar'])->name('registro.store');
    Route::get('password/reset', [ResetPasswordController::class, 'showRequestForm'])->name('password.request');
    Route::post('password/email', [ResetPasswordController::class, 'sendResetLinkEmail'])->name('password.send-link');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'resetPassword'])->name('password.update');
});

// =================================================
// RUTA DE NOTIFICACIONES
// =================================================
Route::post('/notificaciones/leer', function () {
    auth()->user()->unreadNotifications->markAsRead();
    return redirect()->back();
})->name('notificaciones.leer')->middleware('auth');