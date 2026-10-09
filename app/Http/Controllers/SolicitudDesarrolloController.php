<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SolicitudDesarrollo;
use Illuminate\Support\Facades\Auth;
use App\Exports\DesarrollosExport;
use Maatwebsite\Excel\Facades\Excel;

class SolicitudDesarrolloController extends Controller
{
    public function index(Request $request)
    {
        $texto = $request->input('texto');
        $query = SolicitudDesarrollo::with('agente');

        // Seguridad: Si NO es admin, solo ve sus propias solicitudes
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
            $query->where('user_id', Auth::id());
        }

        // Buscador
        if (!empty($texto)) {
            $query->where(function($q) use ($texto) {
                $q->where('descripcion', 'like', "%{$texto}%")
                  ->orWhere('producto_relacionado', 'like', "%{$texto}%")
                  ->orWhereHas('agente', function($q2) use ($texto) {
                      $q2->where('name', 'like', "%{$texto}%");
                  });
            });
        }

        $registros = $query->orderBy('id', 'desc')->paginate(15);
        return view('desarrollo.index', compact('registros', 'texto'));
    }

    public function create()
    {
        return view('desarrollo.action');
    }

    public function store(Request $request)
    {
        // 1. Validamos usando los NOMBRES NUEVOS que manda la vista
        $request->validate([
            'tipo_accion' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'justificacion' => 'required|string',
            'origen_necesidad' => 'required|string|max:255',
        ]);

        $solicitud = new SolicitudDesarrollo();
        $solicitud->user_id = Auth::id();
        
        // 2. TRADUCTOR: Guardamos los datos de la vista en las columnas antiguas de la BD
        $solicitud->tipo_solicitud = $request->tipo_accion; // Traducción
        $solicitud->descripcion = $request->descripcion;
        $solicitud->justificacion = $request->justificacion;
        $solicitud->quien_solicita = $request->origen_necesidad; // Traducción
        
        // Guardamos los opcionales si vienen
        $solicitud->linea_producto = $request->linea_producto;
        $solicitud->producto_relacionado = $request->producto_especifico; // <-- CORREGIDO AL NOMBRE REAL
        $solicitud->cliente_referencia = $request->cliente_referencia;
        
        // Estatus por defecto
        $solicitud->estatus = 'Pendiente / En Revisión';

        $solicitud->save();

        return redirect()->route('desarrollos.index')->with('mensaje', 'Solicitud de desarrollo registrada exitosamente.');
    }

    public function edit($id)
    {
        $registro = SolicitudDesarrollo::findOrFail($id);
        
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
            if ($registro->user_id !== Auth::id()) abort(403, 'No tienes permiso para ver o editar esta solicitud.');
        }

        return view('desarrollo.action', compact('registro'));
    }

    public function update(Request $request, $id)
    {
        $registro = SolicitudDesarrollo::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasRole('admin') && !$user->hasRole('superadmin')) {
            if ($registro->user_id !== $user->id) abort(403, 'No tienes permiso para actualizar esta solicitud.');
        }

        // 1. Validamos usando los NOMBRES NUEVOS
        $request->validate([
            'tipo_accion' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'justificacion' => 'required|string',
            'origen_necesidad' => 'required|string|max:255',
        ]);

        // 2. TRADUCTOR
        $registro->tipo_solicitud = $request->tipo_accion;
        $registro->descripcion = $request->descripcion;
        $registro->justificacion = $request->justificacion;
        $registro->quien_solicita = $request->origen_necesidad;
        
        $registro->linea_producto = $request->linea_producto;
        $registro->producto_relacionado = $request->producto_especifico; // <-- CORREGIDO
        $registro->cliente_referencia = $request->cliente_referencia;

        // Solo si es admin, guardamos el estatus
        if ($user->hasRole('admin') || $user->hasRole('superadmin')) {
            if ($request->has('estatus')) {
                $registro->estatus = $request->input('estatus');
            }
        }

        $registro->save();

        return redirect()->route('desarrollos.index')->with('mensaje', 'Solicitud actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $registro = SolicitudDesarrollo::findOrFail($id);
        
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
             abort(403, 'Solo el administrador puede eliminar estas solicitudes.');
        }

        $registro->delete();
        return redirect()->route('desarrollos.index')->with('mensaje', 'Solicitud eliminada correctamente.');
    }

    // Generar Excel
    public function exportar()
    {
        return Excel::download(new DesarrollosExport, 'Solicitudes_Desarrollo_ARDA.xlsx');
    }

    // Cambio rápido de estatus desde el Modal
    public function cambiarEstado(Request $request, $id)
    {
        $registro = \App\Models\SolicitudDesarrollo::findOrFail($id);
        
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
            abort(403, 'Solo los administradores pueden cambiar el estatus de las solicitudes.');
        }

        $request->validate([
            'estatus' => 'required|string'
        ]);

        $registro->estatus = $request->input('estatus');
        $registro->save();

        return redirect()->back()->with('mensaje', 'El estatus de la solicitud se actualizó correctamente.');
    }

    // Generar Ficha PDF
    public function exportarFichaPdf($id)
    {
        $registro = \App\Models\SolicitudDesarrollo::with('agente')->findOrFail($id);
        
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
            if ($registro->user_id !== Auth::id()) abort(403, 'No tienes permiso para ver esto.');
        }

        $logoBase64 = null;
        try {
            $pathLogo = public_path('assets/img/LOGO.png');
            if (!file_exists($pathLogo)) {
                $pathLogo = base_path('../public_html/assets/img/LOGO.png');
            }
            if (file_exists($pathLogo)) {
                $type = pathinfo($pathLogo, PATHINFO_EXTENSION);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($pathLogo));
            }
        } catch (\Exception $e) {}

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('desarrollo.pdf', compact('registro', 'logoBase64'));
        $pdf->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);
        
        return $pdf->stream('Solicitud-Desarrollo-'. $registro->id .'.pdf');
    }
}