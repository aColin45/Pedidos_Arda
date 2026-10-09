<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EncuestaRespuesta;
use App\Models\User; // Para listar los agentes
use Illuminate\Support\Facades\Auth;

class EncuestaController extends Controller
{
    // Ver resultados (Solo Admin/Superadmin)
    public function index()
    {
        $this->authorizeAdmin();

        // Obtenemos todos los agentes y sus respuestas
        $agentes = User::role('agente-ventas')->with('encuestaRespuesta')->get();
        $respuestas = EncuestaRespuesta::with('user')->latest()->get();

        return view('encuesta.index', compact('agentes', 'respuestas'));
    }

    public function responder()
    {
        $user = Auth::user();
        
        // El admin siempre puede entrar a ver la (previsualización)
        if ($user->hasRole('admin') || $user->hasRole('superadmin')) {
            return view('encuesta.responder');
        }

        // El agente solo entra si no ha contestado
        if ($user->haContestadoEncuesta()) {
            return redirect('/dashboard')->with('info', 'Gracias, ya has completado esta encuesta.');
        }

        return view('encuesta.responder');
    }

    public function guardar(Request $request)
    {
        // Si es admin, no guardamos la respuesta (solo previsualiza)
        if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('superadmin')) {
            return redirect('/dashboard')->with('info', 'Previsualización finalizada. No se guardaron datos por ser Administrador.');
        }

        EncuestaRespuesta::create([
            'user_id' => Auth::id(),
            'respuestas' => $request->except('_token')
        ]);

        return redirect('/dashboard')->with('success', '¡Encuesta guardada con éxito!');
    }

    private function authorizeAdmin() {
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('superadmin')) {
            abort(403, 'No tienes permiso para ver esta sección.');
        }
    }

    public function detalle($id) {
        $this->authorizeAdmin();

        // Buscamos la respuesta por su ID y cargamos al usuario relacionado
        $respuesta = EncuestaRespuesta::with('user')->findOrFail($id);

        return view('encuesta.detalle', compact('respuesta'));
    }

    public function exportar()
    {
        $this->authorizeAdmin();

        // Obtenemos los agentes junto con sus respuestas
        $agentes = User::role('agente-ventas')->with('encuestaRespuesta')->get();

        // Nombre del archivo profesional (.xls nativo compatible con hojas múltiples)
        $fileName = 'Reporte_Sondeo_Mercado_ARDA_' . date('d-m-Y') . '.xls';

        $headers = [
            "Content-type"        => "application/vnd.ms-excel; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($agentes) {
            // Aseguramos limpiar cualquier residuo o espacio en blanco previo del búfer del servidor
            if (ob_get_level() > 0) {
                ob_clean();
            }

            // Inicializamos la estructura XML compatible con hojas múltiples de Excel
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            echo ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
            echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
            echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            echo ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";

            // DEFINICIÓN DE ESTILOS VISUALES (Colores corporativos y fuentes)
            echo ' <Styles>' . "\n";
            echo '  <Style ss:ID="Default" ss:Name="Normal">' . "\n";
            echo '   <Alignment ss:Vertical="Center"/>' . "\n";
            echo '   <Font ss:FontName="Calibri" ss:Size="11" ss:Color="#000000"/>' . "\n";
            echo '  </Style>' . "\n";
            echo '  <Style ss:ID="Titulo">' . "\n";
            echo '   <Font ss:FontName="Calibri" ss:Size="16" ss:Bold="1" ss:Color="#1F497D"/>' . "\n";
            echo '  </Style>' . "\n";
            echo '  <Style ss:ID="Subtitulo">' . "\n";
            echo '   <Font ss:FontName="Calibri" ss:Size="11" ss:Italic="1" ss:Color="#595959"/>' . "\n";
            echo '  </Style>' . "\n";
            echo '  <Style ss:ID="Header">' . "\n";
            echo '   <Interior ss:Color="#1F497D" ss:Pattern="Solid"/>' . "\n";
            echo '   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#FFFFFF"/>' . "\n";
            echo '   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>' . "\n";
            echo '   <Borders>' . "\n";
            echo '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BFBFBF"/>' . "\n";
            echo '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BFBFBF"/>' . "\n";
            echo '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BFBFBF"/>' . "\n";
            echo '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BFBFBF"/>' . "\n";
            echo '   </Borders>' . "\n";
            echo '  </Style>' . "\n";
            echo '  <Style ss:ID="CeldaNormal">' . "\n";
            echo '   <Alignment ss:Vertical="Center" ss:WrapText="1"/>' . "\n";
            echo '   <Borders>' . "\n";
            echo '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '   </Borders>' . "\n";
            echo '  </Style>' . "\n";
            echo '  <Style ss:ID="CeldaZebra">' . "\n";
            echo '   <Interior ss:Color="#F2F5F8" ss:Pattern="Solid"/>' . "\n";
            echo '   <Alignment ss:Vertical="Center" ss:WrapText="1"/>' . "\n";
            echo '   <Borders>' . "\n";
            echo '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>' . "\n";
            echo '   </Borders>' . "\n";
            echo '  </Style>' . "\n";
            echo ' </Styles>' . "\n";

            // =========================================================
            // HOJA 1: ESTRUCTURA DE LA ENCUESTA
            // =========================================================
            echo ' <Worksheet ss:Name="Estructura de Encuesta">' . "\n";
            echo '  <Table>' . "\n";
            echo '   <Column ss:Width="70"/>' . "\n"; 
            echo '   <Column ss:Width="320"/>' . "\n"; 
            echo '   <Column ss:Width="380"/>' . "\n"; 
            
            echo '   <Row ss:Height="25"><Cell ss:StyleID="Titulo"><Data ss:Type="String">GRUPO INDUSTRIAL ARDA S.A. DE C.V.</Data></Cell></Row>' . "\n";
            echo '   <Row ss:Height="20"><Cell ss:StyleID="Subtitulo"><Data ss:Type="String">Diseño Oficial de Preguntas del Formulario</Data></Cell></Row>' . "\n";
            echo '   <Row/>' . "\n"; 
            
            echo '   <Row ss:Height="25">' . "\n";
            echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">ID</Data></Cell>' . "\n";
            echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">Pregunta Oficial</Data></Cell>' . "\n";
            echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">Opciones de Respuesta / Parámetros</Data></Cell>' . "\n";
            echo '   </Row>' . "\n";

            $preguntasBase = [
                ['P1', '¿Qué tipo de canal conforman la mayoría de tus clientes? (puedes marcar hasta 2)', 'Distribuidores, Mayoristas, Ferreterías independientes, Tlapalerías, Tiendas de materiales para construcción, Otros'],
                ['P2', '¿Tus clientes actualmente venden o manejan accesorios de gas LP?', 'Sí, la mayoría | Algunos | Muy pocos / ninguno'],
                ['P3', '¿Qué tan importante sería para tus clientes tener reguladores de gas de una vía en su catálogo?', 'Muy importante | Importante, pero no indispensable | Poco importante | No saben / no les importa'],
                ['P4', '¿Con qué frecuencia te han pedido tus clientes reguladores de gas LP?', 'Muy Frecuentemente | Ocasionalmente | Rara vez | Nunca me lo han pedido'],
                ['P5', '¿Qué rango de precio al público crees que aceptaría tu mercado para un regulador de calidad con certificación NOM?', '$150 MXN | $151–$160 MXN | $161–$200 MXN'],
                ['P6', '¿A qué precio de mayoreo venden esos reguladores tus clientes actualmente (lo que ellos pagan)?', 'No tengo ese dato | Menos de $100 MXN | $120–$150 MXN | Más de $170 MXN'],
                ['P7', 'Si pudieras ofrecer manguera + regulador como kit, ¿crees que tus clientes lo comprarían junto?', 'Sí, sería más fácil venderlos así | Algunos preferirían el kit, otros por separado | No creo, prefieren comprar suelto | No sé, nunca lo he ofrecido así'],
                ['Comentarios', 'Comentarios adicionales o solicitudes frecuentes de tus clientes:', 'Texto libre libremente ingresado por el agente corporativo']
            ];

            foreach($preguntasBase as $index => $p) {
                $style = ($index % 2 == 0) ? 'CeldaNormal' : 'CeldaZebra';
                echo '   <Row ss:Height="30">' . "\n";
                echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.$p[0].'</Data></Cell>' . "\n";
                echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.htmlspecialchars($p[1], ENT_QUOTES, 'UTF-8').'</Data></Cell>' . "\n";
                echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.htmlspecialchars($p[2], ENT_QUOTES, 'UTF-8').'</Data></Cell>' . "\n";
                echo '   </Row>' . "\n";
            }
            echo '  </Table>' . "\n";
            echo ' </Worksheet>' . "\n";

            // =========================================================
            // HOJA 2: MATRIZ DE RESPUESTAS DETALLADAS
            // =========================================================
            echo ' <Worksheet ss:Name="Matriz de Respuestas">' . "\n";
            echo '  <Table>' . "\n";
            echo '   <Column ss:Width="140"/>' . "\n"; 
            echo '   <Column ss:Width="180"/>' . "\n"; 
            echo '   <Column ss:Width="130"/>' . "\n"; 
            echo '   <Column ss:Width="200"/>' . "\n"; 
            echo '   <Column ss:Width="100"/>' . "\n"; 
            echo '   <Column ss:Width="130"/>' . "\n"; 
            echo '   <Column ss:Width="150"/>' . "\n"; 
            echo '   <Column ss:Width="130"/>' . "\n"; 
            echo '   <Column ss:Width="120"/>' . "\n"; 
            echo '   <Column ss:Width="120"/>' . "\n"; 
            echo '   <Column ss:Width="150"/>' . "\n"; 
            echo '   <Column ss:Width="250"/>' . "\n"; 

            echo '   <Row ss:Height="25"><Cell ss:StyleID="Titulo"><Data ss:Type="String">MATRIZ GENERAL DE RESPUESTAS POR AGENTE</Data></Cell></Row>' . "\n";
            // CORREGIDO: </Row> con mayúscula finaliza correctamente la etiqueta estructurada
            echo '   <Row ss:Height="20"><Cell ss:StyleID="Subtitulo"><Data ss:Type="String">Reporte consolidado para la toma de decisiones estratégicas ARDA</Data></Cell></Row>' . "\n";
            echo '   <Row/>' . "\n";

            echo '   <Row ss:Height="25">' . "\n";
            $headersTab2 = [
                'Agente de Ventas', 'Email', 'Fecha de Respuesta', 'Q1: Canales', 'Q1: Otros',
                'Q2: Venden Accesorios', 'Q3: Importancia', 'Q4: Frecuencia', 'Q5: Precio Público',
                'Q6: Precio Mayoreo', 'Q7: Aceptación de Kit', 'Comentarios Adicionales'
            ];
            foreach($headersTab2 as $head) {
                echo '    <Cell ss:StyleID="Header"><Data ss:Type="String">'.$head.'</Data></Cell>' . "\n";
            }
            echo '   </Row>' . "\n";

            foreach ($agentes as $index => $agente) {
                if ($agente->encuestaRespuesta) {
                    $r = $agente->encuestaRespuesta->respuestas;
                    $style = ($index % 2 == 0) ? 'CeldaNormal' : 'CeldaZebra';

                    $p1 = isset($r['p1_canal']) ? (is_array($r['p1_canal']) ? implode(', ', $r['p1_canal']) : $r['p1_canal']) : '---';

                    echo '   <Row ss:Height="35">' . "\n";
                    echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.htmlspecialchars($agente->name, ENT_QUOTES, 'UTF-8').'</Data></Cell>' . "\n";
                    echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.htmlspecialchars($agente->email, ENT_QUOTES, 'UTF-8').'</Data></Cell>' . "\n";
                    echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.$agente->encuestaRespuesta->created_at->format('d/m/Y h:i A').'</Data></Cell>' . "\n";
                    echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.htmlspecialchars($p1, ENT_QUOTES, 'UTF-8').'</Data></Cell>' . "\n";
                    echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.htmlspecialchars($r['p1_otros'] ?? '---', ENT_QUOTES, 'UTF-8').'</Data></Cell>' . "\n";
                    echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.htmlspecialchars($r['p2'] ?? '---', ENT_QUOTES, 'UTF-8').'</Data></Cell>' . "\n";
                    echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.htmlspecialchars($r['p3'] ?? '---', ENT_QUOTES, 'UTF-8').'</Data></Cell>' . "\n";
                    echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.htmlspecialchars($r['p4'] ?? '---', ENT_QUOTES, 'UTF-8').'</Data></Cell>' . "\n";
                    echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.htmlspecialchars($r['p5'] ?? '---', ENT_QUOTES, 'UTF-8').'</Data></Cell>' . "\n";
                    echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.htmlspecialchars($r['p6'] ?? '---', ENT_QUOTES, 'UTF-8').'</Data></Cell>' . "\n";
                    echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.htmlspecialchars($r['p7'] ?? '---', ENT_QUOTES, 'UTF-8').'</Data></Cell>' . "\n";
                    echo '    <Cell ss:StyleID="'.$style.'"><Data ss:Type="String">'.htmlspecialchars($r['comentarios'] ?? '---', ENT_QUOTES, 'UTF-8').'</Data></Cell>' . "\n";
                    echo '   </Row>' . "\n";
                }
            }

            echo '  </Table>' . "\n";
            echo ' </Worksheet>' . "\n";
            echo '</Workbook>' . "\n";
            
            // Forzamos la detención para que Laravel no inyecte scripts adicionales o HTML de rastreo al final del archivo
            exit(); 
        };

        return response()->stream($callback, 200, $headers);
    }
}