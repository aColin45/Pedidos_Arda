<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prospecto;
use Illuminate\Support\Facades\Auth;
use App\Exports\ProspectosExport;
use Maatwebsite\Excel\Facades\Excel;

class ProspectoController extends Controller
{
    public function index(Request $request)
    {
        $texto = $request->input('texto');
        $query = Prospecto::with('agente'); // Cargamos al agente relacionado

        // Seguridad: Si NO es admin, solo ve sus propios prospectos
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
            $query->where('user_id', Auth::id());
        }

        // Buscador
        if (!empty($texto)) {
            $query->where(function($q) use ($texto) {
                $q->where('razon_social', 'like', "%{$texto}%")
                  ->orWhere('nombre_comercial', 'like', "%{$texto}%")
                  ->orWhere('contacto_nombre', 'like', "%{$texto}%")
                  ->orWhere('rfc', 'like', "%{$texto}%");
            });
        }

        $registros = $query->orderBy('id', 'desc')->paginate(15);
        return view('prospecto.index', compact('registros', 'texto'));
    }

    public function create()
    {
        return view('prospecto.action');
    }

    public function store(Request $request)
    {
        // Validaciones obligatorias mínimas para no dejar el registro en blanco
        $request->validate([
            'razon_social' => 'required|string|max:255',
            'giro_negocio' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
            'municipio' => 'required|string|max:255',
            'contacto_nombre' => 'required|string|max:255',
            'contacto_puesto' => 'required|string|max:255',
            'origen_prospecto' => 'required|string|max:255',
            'fecha_primer_contacto' => 'required|date',
            'estatus' => 'nullable|string', // Validar estatus
            'motivo_descarte' => 'nullable|string' // Validar el nuevo campo
        ]);

        $prospecto = new Prospecto($request->all());
        $prospecto->user_id = Auth::id(); // Guardamos quién lo registró
        
        // Manejo especial para los checkboxes de líneas de interés
        if (!$request->has('lineas_interes')) {
            $prospecto->lineas_interes = [];
        }

        // --- NUEVO: Lógica de guardado del motivo ---
        if ($request->input('estatus') === 'descartado' && $request->has('motivo_descarte')) {
            $prospecto->motivo_descarte = $request->input('motivo_descarte');
        } else {
            $prospecto->motivo_descarte = null;
        }

        $prospecto->save();

        return redirect()->route('prospectos.index')->with('mensaje', 'Prospecto registrado exitosamente.');
    }

    public function edit($id)
    {
        $registro = Prospecto::findOrFail($id);
        
        // Evitar que un agente edite prospectos de otro agente
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
            if ($registro->user_id !== Auth::id()) {
                abort(403, 'No tienes permiso para editar este prospecto.');
            }
        }

        return view('prospecto.action', compact('registro'));
    }

    public function update(Request $request, $id)
    {
        $registro = Prospecto::findOrFail($id);

        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
            if ($registro->user_id !== Auth::id()) {
                abort(403, 'No tienes permiso para actualizar este prospecto.');
            }
        }

        $request->validate([
            'razon_social' => 'required|string|max:255',
            'giro_negocio' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
            'municipio' => 'required|string|max:255',
            'contacto_nombre' => 'required|string|max:255',
            'contacto_puesto' => 'required|string|max:255',
            'origen_prospecto' => 'required|string|max:255',
            'fecha_primer_contacto' => 'required|date',
            'estatus' => 'nullable|string', // Validar estatus
            'motivo_descarte' => 'nullable|string' // Validar el nuevo campo
        ]);

        $registro->fill($request->all());

        if (!$request->has('lineas_interes')) {
            $registro->lineas_interes = [];
        }

        // --- NUEVO: Lógica de guardado del motivo ---
        if ($request->input('estatus') === 'descartado' && $request->has('motivo_descarte')) {
            $registro->motivo_descarte = $request->input('motivo_descarte');
        } else {
            $registro->motivo_descarte = null; // Si cambia de estado, borramos el motivo anterior
        }

        $registro->save();

        return redirect()->route('prospectos.index')->with('mensaje', 'Prospecto actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $registro = Prospecto::findOrFail($id);
        
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
             abort(403, 'No tienes permiso para eliminar prospectos.');
        }

        $registro->delete();
        return redirect()->route('prospectos.index')->with('mensaje', 'Prospecto eliminado.');
    }

    // Exportar a Excel
    public function exportar()
    {
        return Excel::download(new ProspectosExport, 'Prospectos_ARDA.xlsx');
    }

    // Cambio rápido de estado desde el modal
    public function cambiarEstado(Request $request, $id)
    {
        $registro = Prospecto::findOrFail($id);
        
        // Seguridad
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
            if ($registro->user_id !== Auth::id()) {
                abort(403, 'No tienes permiso para modificar este prospecto.');
            }
        }

        $request->validate([
            'estatus' => 'required|string|in:nuevo,en_seguimiento,convertido,descartado',
            'motivo_descarte' => 'nullable|string' // Validar el nuevo campo que vendrá del modal
        ]);

        $registro->estatus = $request->input('estatus');
        
        // --- Guardamos el motivo si es descartado ---
        if ($request->input('estatus') === 'descartado' && $request->has('motivo_descarte')) {
            $registro->motivo_descarte = $request->input('motivo_descarte');
        } elseif ($request->input('estatus') !== 'descartado') {
             $registro->motivo_descarte = null; // Si cambia de descartado a otro, borramos el motivo
        }

        $registro->save();

        return redirect()->back()->with('mensaje', 'El estatus del prospecto se actualizó correctamente.');
    }

    // =======================================================
    // Generar Ficha PDF Individual
    // =======================================================
    public function exportarFichaPdf($id)
    {
        $registro = Prospecto::with('agente')->findOrFail($id);
        
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
            if ($registro->user_id !== Auth::id()) {
                abort(403, 'No tienes permiso para ver este prospecto.');
            }
        }

        // Obtener el Logo en Base64 de forma robusta
        $logoBase64 = null;
        try {
            $pathLogo = public_path('assets/img/LOGO.png');
            if (!file_exists($pathLogo)) {
                $pathLogo = base_path('../public_html/assets/img/LOGO.png');
            }
            if (file_exists($pathLogo)) {
                $type = pathinfo($pathLogo, PATHINFO_EXTENSION);
                $data = file_get_contents($pathLogo);
                if ($data !== false) {
                    $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            }
        } catch (\Exception $e) {}

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('prospecto.pdf', compact('registro', 'logoBase64'));
        $pdf->setOptions(['dpi' => 150, 'defaultFont' => 'sans-serif']);
        
        return $pdf->stream('Ficha-Prospecto-'. $registro->razon_social .'.pdf');
    }

    // =======================================================
    // Convertir Prospecto directamente a Cliente Oficial
    // =======================================================
    public function convertirACliente(Request $request, $id)
    {
        $prospecto = Prospecto::findOrFail($id);
        
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
            abort(403, 'Solo un administrador puede dar de alta clientes formales.');
        }

        $request->validate([
            'codigo' => 'required|string|unique:clientes,codigo',
            'descuento' => 'required|numeric',
            'contacto' => 'nullable|string',
            'monto_credito' => 'nullable|numeric',
            'dias_credito' => 'nullable|integer',
            'fecha_otorgamiento' => 'nullable|date',
            'referencia_bancaria' => 'nullable|string'
        ], [
            'codigo.unique' => 'Ese Código de Cliente ya está registrado en el sistema.'
        ]);

        // Clonamos la información a la tabla de Clientes
        $cliente = new \App\Models\Cliente();
        $cliente->user_id = $prospecto->user_id; // Se lo asignamos al agente que lo prospectó
        $cliente->codigo = mb_strtoupper($request->input('codigo'));
        $cliente->nombre = $prospecto->razon_social;
        $cliente->rfc = $prospecto->rfc ?? 'XAXX010101000';
        $cliente->email = $prospecto->email;
        $cliente->telefono = $prospecto->telefono ?? $prospecto->celular;
        $cliente->direccion = trim($prospecto->calle_numero . ' ' . $prospecto->colonia);
        $cliente->ciudad = $prospecto->ciudad;
        $cliente->estado = $prospecto->estado;
        $cliente->cp = $prospecto->cp;
        
        // Campos financieros obtenidos del Modal
        $cliente->contacto = $request->input('contacto'); // Usado para Lista de Precios
        $cliente->descuento = $request->input('descuento', 0);
        $cliente->monto_credito = $request->input('monto_credito', 0);
        $cliente->dias_credito = $request->input('dias_credito', 0);
        $cliente->fecha_otorgamiento = $request->input('fecha_otorgamiento');
        $cliente->referencia_bancaria = $request->input('referencia_bancaria');
        
        $cliente->activo = true;
        $cliente->save();

        // Actualizamos el prospecto para saber que ya fue procesado
        $prospecto->estatus = 'convertido';
        $prospecto->save();

        return redirect()->back()->with('mensaje', '¡Éxito! Cliente creado. (ID: ' . $cliente->id . ' | Código: ' . $cliente->codigo . ') Ya aparece en la Gestión de Clientes.');
    }
}