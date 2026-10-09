<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visita;
use App\Models\Prospecto;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;
use App\Exports\VisitasExport;
use Maatwebsite\Excel\Facades\Excel;

class VisitaController extends Controller
{
    public function index(Request $request)
    {
        $texto = $request->input('texto');
        $query = Visita::with(['agente', 'prospecto', 'cliente']); 

        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
            $query->where('user_id', Auth::id());
        }

        if (!empty($texto)) {
            $query->where(function($q) use ($texto) {
                $q->where('objetivo_visita', 'like', "%{$texto}%")
                  ->orWhere('resultado_visita', 'like', "%{$texto}%")
                  ->orWhereHas('prospecto', function($q2) use ($texto) {
                      $q2->where('razon_social', 'like', "%{$texto}%");
                  })
                  ->orWhereHas('cliente', function($q3) use ($texto) {
                      $q3->where('nombre', 'like', "%{$texto}%");
                  });
            });
        }

        $registros = $query->orderBy('fecha_visita', 'desc')->paginate(15);
        return view('visita.index', compact('registros', 'texto'));
    }

    public function create()
    {
        $user = Auth::user();
        if ($user->hasRole('admin') || $user->hasRole('superadmin')) {
            $prospectos = Prospecto::orderBy('razon_social')->get();
            $clientes = Cliente::orderBy('nombre')->get();
        } else {
            $prospectos = Prospecto::where('user_id', $user->id)->orderBy('razon_social')->get();
            $clientes = Cliente::where('user_id', $user->id)->orderBy('nombre')->get();
        }
        return view('visita.action', compact('prospectos', 'clientes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_visita' => 'required|date',
            'objetivo' => 'required|string|max:255',
            'resultado' => 'required|string|max:255',
            'modo_contacto' => 'required|string|max:255',
            'motivo_rechazo' => 'nullable|string',
        ]);

        if (!$request->prospecto_id && !$request->cliente_id) {
            return back()->withErrors(['entidad' => 'Debe seleccionar un Prospecto o un Cliente.'])->withInput();
        }

        $visita = new Visita();
        $visita->user_id = Auth::id();
        
        // TRADUCTOR: Formulario -> Base de Datos
        $visita->fecha_visita = $request->fecha_visita;
        $visita->es_prospecto = $request->has('es_prospecto') ? $request->es_prospecto : ($request->prospecto_id ? 1 : 0);
        $visita->prospecto_id = $visita->es_prospecto ? $request->prospecto_id : null;
        $visita->cliente_id = !$visita->es_prospecto ? $request->cliente_id : null;
        
        $visita->atendio_nombre = $request->persona_atendio;
        $visita->atendio_cargo = $request->cargo_atendio;
        $visita->objetivo_visita = $request->objetivo;
        $visita->resultado_visita = $request->resultado;

        // --- NUEVO: Guardar modo de contacto y motivo de rechazo ---
        $visita->modo_contacto = $request->modo_contacto;
        if ($request->resultado === 'No interesado (Rechazo)' && $request->has('motivo_rechazo')) {
            $visita->motivo_rechazo = $request->motivo_rechazo;
        } else {
            $visita->motivo_rechazo = null;
        }
        // -----------------------------------------------------------
        
        $visita->lineas_rotacion = is_array($request->lineas_interes) ? implode(', ', $request->lineas_interes) : null;
        $visita->productos_especificos = $request->productos_especificos;
        $visita->marcas_competencia = $request->marcas_competencia;
        $visita->necesidades_detectadas = $request->necesidades_problemas;
        $visita->monto_generado = $request->monto_pedido ?? 0;
        $visita->fecha_proximo_seguimiento = $request->proximo_contacto;
        $visita->acuerdos = $request->compromisos;
        $visita->tipo_contacto = 'Visita'; // Valor por defecto para campo antiguo

        $visita->save();

        return redirect()->route('visitas.index')->with('mensaje', 'Visita registrada exitosamente.');
    }

    public function edit($id)
    {
        $registro = Visita::findOrFail($id);
        $user = Auth::user();
        
        if (!$user->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
            if ($registro->user_id !== $user->id) abort(403, 'No tienes permiso.');
        }

        // TRADUCTOR INVERSO: Base de Datos -> Vista
        $registro->persona_atendio = $registro->atendio_nombre;
        $registro->cargo_atendio = $registro->atendio_cargo;
        $registro->objetivo = $registro->objetivo_visita;
        $registro->resultado = $registro->resultado_visita;
        $registro->motivo_rechazo = $registro->motivo_rechazo;
        $registro->modo_contacto = $registro->modo_contacto;
        $registro->lineas_interes = $registro->lineas_rotacion;
        $registro->necesidades_problemas = $registro->necesidades_detectadas;
        $registro->monto_pedido = $registro->monto_generado;
        $registro->proximo_contacto = $registro->fecha_proximo_seguimiento;
        $registro->compromisos = $registro->acuerdos;

        if ($user->hasRole('admin') || $user->hasRole('superadmin')) {
            $prospectos = Prospecto::orderBy('razon_social')->get();
            $clientes = Cliente::orderBy('nombre')->get();
        } else {
            $prospectos = Prospecto::where('user_id', $user->id)->orderBy('razon_social')->get();
            $clientes = Cliente::where('user_id', $user->id)->orderBy('nombre')->get();
        }
        return view('visita.action', compact('registro', 'prospectos', 'clientes'));
    }

    public function update(Request $request, $id)
    {
        $registro = Visita::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
            if ($registro->user_id !== $user->id) abort(403, 'No tienes permiso.');
        }

        $request->validate([
            'fecha_visita' => 'required|date',
            'objetivo' => 'required|string|max:255',
            'resultado' => 'required|string|max:255',
            'modo_contacto' => 'required|string|max:255',
            'motivo_rechazo' => 'nullable|string',
        ]);

        // TRADUCTOR: Formulario -> Base de Datos
        $registro->fecha_visita = $request->fecha_visita;
        $registro->es_prospecto = $request->has('es_prospecto') ? $request->es_prospecto : ($request->prospecto_id ? 1 : 0);
        $registro->prospecto_id = $registro->es_prospecto ? $request->prospecto_id : null;
        $registro->cliente_id = !$registro->es_prospecto ? $request->cliente_id : null;
        
        $registro->atendio_nombre = $request->persona_atendio;
        $registro->atendio_cargo = $request->cargo_atendio;
        $registro->objetivo_visita = $request->objetivo;
        $registro->resultado_visita = $request->resultado;

        // --- NUEVO: Guardar modo de contacto y motivo de rechazo ---
        $registro->modo_contacto = $request->modo_contacto;
        if ($request->resultado === 'No interesado (Rechazo)' && $request->has('motivo_rechazo')) {
            $registro->motivo_rechazo = $request->motivo_rechazo;
        } else {
            $registro->motivo_rechazo = null;
        }
        // -----------------------------------------------------------
        
        $registro->lineas_rotacion = is_array($request->lineas_interes) ? implode(', ', $request->lineas_interes) : null;
        $registro->productos_especificos = $request->productos_especificos;
        $registro->marcas_competencia = $request->marcas_competencia;
        $registro->necesidades_detectadas = $request->necesidades_problemas;
        $registro->monto_generado = $request->monto_pedido ?? 0;
        $registro->fecha_proximo_seguimiento = $request->proximo_contacto;
        $registro->acuerdos = $request->compromisos;
        
        $registro->save();

        return redirect()->route('visitas.index')->with('mensaje', 'Visita actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $registro = Visita::findOrFail($id);
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) abort(403);
        $registro->delete();
        return redirect()->route('visitas.index')->with('mensaje', 'Visita eliminada.');
    }

    public function exportar()
    {
        return Excel::download(new VisitasExport, 'Bitacora_Visitas_ARDA.xlsx');
    }

    public function exportarFichaPdf($id)
    {
        $registro = Visita::with(['agente', 'cliente', 'prospecto'])->findOrFail($id);
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
            if ($registro->user_id !== Auth::id()) abort(403);
        }

        // TRADUCTOR INVERSO: Base de Datos -> Vista (Para el PDF)
        $registro->persona_atendio = $registro->atendio_nombre;
        $registro->cargo_atendio = $registro->atendio_cargo;
        $registro->objetivo = $registro->objetivo_visita;
        $registro->resultado = $registro->resultado_visita;
        $registro->lineas_interes = $registro->lineas_rotacion;
        $registro->necesidades_problemas = $registro->necesidades_detectadas;
        $registro->monto_pedido = $registro->monto_generado;
        $registro->proximo_contacto = $registro->fecha_proximo_seguimiento;
        $registro->compromisos = $registro->acuerdos;

        $logoBase64 = null;
        try {
            $pathLogo = public_path('assets/img/LOGO.png');
            if (file_exists($pathLogo)) {
                $type = pathinfo($pathLogo, PATHINFO_EXTENSION);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($pathLogo));
            }
        } catch (\Exception $e) {}

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('visita.pdf', compact('registro', 'logoBase64'));
        $pdf->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);
        return $pdf->stream('Reporte-Visita-'. $registro->id .'.pdf');
    }
}