<?php

namespace App\Http\Controllers;

use App\Models\InstructorFichaCaracterizacion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AsistenciaAprendiz;
use App\Models\ParametroTema;
use App\Models\FichaCaracterizacion;
use Herramientas;
use Illuminate\Support\Facades\Log;
use App\Models\Aprendiz;
use App\Models\Persona;
use App\Models\Instructor;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Services\AsistenceQrService;
use App\Events\NuevaAsistenciaRegistrada;
use App\Events\QrScanned;
use App\Models\Evidencias;
use App\Services\RegistroActividadesServices;

class AsistenceQrController extends Controller
{

    protected $asistenceQrService;
    protected $registroActividadesService;

    public function __construct(AsistenceQrService $asistenceQrService, RegistroActividadesServices $registroActividadesService)
    {
        $this->asistenceQrService = $asistenceQrService;
        $this->registroActividadesService = $registroActividadesService;
        $this->middleware('auth');
    }

    /**
     * Muestra una lista de todas las fichas de caracterización.
     *
     * Este método recupera todas las fichas de caracterización junto con su
     * relación 'programaFormacion' y las pasa a la vista 'fichas.index'.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse La vista que muestra la lista de fichas de caracterización o redirección con mensaje.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Log para debugging
        Log::info('=== DEBUG QR_ASISTENCE.CARACTER_SELECTER INDEX ===');
        Log::info('Usuario ID: ' . $user->id);
        Log::info('Usuario Persona ID: ' . ($user->persona_id ?? 'NULL'));
        Log::info('Usuario email: ' . $user->email);
        
        // Verificar si el usuario tiene persona
        if (!$user->persona_id) {
            Log::warning('El usuario no tiene persona_id asociado');
        }
        
        $instructorFicha = $this->asistenceQrService->getInstructorFichaIndex($user);
        $diasFormacion = $this->asistenceQrService->getDiasFormacion();
        
        // Log resultados
        Log::info('Resultado instructorFicha: ' . ($instructorFicha ? 'TIENE DATOS' : 'NULL'));
        if ($instructorFicha) {
            Log::info('Cantidad de fichas: ' . $instructorFicha->count());
            if ($instructorFicha->isNotEmpty()) {
                foreach ($instructorFicha as $index => $ficha) {
                    Log::info("Ficha {$index}: ID={$ficha->id}, instructor_id={$ficha->instructor_id}, ficha_id={$ficha->ficha_id}");
                }
            }
        }
        
        Log::info('Dias de formación: ' . json_encode($diasFormacion));
        Log::info('=== FIN DEBUG ===');

        if (!$instructorFicha) {
            return view('qr_asistence.caracter_selecter', compact('instructorFicha', 'diasFormacion'))
                ->with('warning', 'No tienes fichas de caracterización asignadas. Contacta al administrador.');
        }

        return view('qr_asistence.caracter_selecter', compact('instructorFicha', 'diasFormacion'));
    }

    /**
     * Muestra la vista para seleccionar la caracterización.
     *
     * @param int $id El ID de la caracterización.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse La vista de selección de caracterización o redirección de error.
     */
    public function caracterSelected(InstructorFichaCaracterizacion $caracterizacion, $asistencia_id = null)
    {
        try {
            Log::info('=== DEBUG CARACTERSELECTED ===');
            Log::info('Caracterizacion ID desde route: ' . $caracterizacion->id);
            Log::info('Caracterizacion tipo: ' . get_class($caracterizacion));

            // Soportar asistencia_id por querystring: /caracterSelected/{caracterizacion}?asistencia_id=123
            if (!$asistencia_id) {
                $asistencia_id = request()->query('asistencia_id');
            }

            Log::info('Asistencia ID (route/query): ' . ($asistencia_id ?? 'NULL'));
            
            // Si no se proporciona asistencia, buscar la asistencia activa o crear una nueva
            if (!$asistencia_id) {
                Log::info('Buscando asistencia activa para ficha_id: ' . $caracterizacion->ficha_id);
                
                // Buscar asistencia activa para esta ficha
                $asistencia = \App\Models\Asistencia::deFicha($caracterizacion->ficha_id)
                    ->activa()
                    ->first();
                
                Log::info('Resultado búsqueda asistencia activa: ' . ($asistencia ? 'ENCONTRADA ID: ' . $asistencia->id : 'NO ENCONTRADA'));
                
                if (!$asistencia) {
                    // No hay asistencia activa, crear una nueva
                    Log::info('Creando nueva evidencia y asistencia...');
                    
                    $evidencia = \App\Models\Evidencias::create([
                        'nombre' => 'Evidencia por defecto',
                        'id_estado' => 1,
                        'fecha_evidencia' => now(),
                        'user_create_id' => Auth::id(),
                        'user_edit_id' => Auth::id(),
                    ]);
                    
                    Log::info('Evidencia creada: ' . $evidencia->id);
                    
                    $asistencia = \App\Models\Asistencia::create([
                        'evidencia_id' => $evidencia->id,
                        'instructor_ficha_id' => $caracterizacion->ficha_id,
                        'fecha' => now()->toDateString(),
                        'hora_inicio' => now(),
                        'is_finished' => false,
                        'user_create_id' => Auth::id(),
                        'user_edit_id' => Auth::id(),
                    ]);
                    
                    Log::info('Nueva asistencia creada: ' . $asistencia->id);
                } else {
                    Log::info('Asistencia activa encontrada: ' . $asistencia->id);
                }
            } else {
                // Se proporcionó asistencia_id, buscar esa asistencia específica
                $asistencia = \App\Models\Asistencia::find($asistencia_id);
                
                if (!$asistencia) {
                    return redirect()->back()->with('error', 'Asistencia no encontrada.');
                }
                
                Log::info('Asistencia específica encontrada: ' . $asistencia->id);
            }
            
            // Obtener la evidencia desde la asistencia
            $evidencia = $asistencia->evidencia;
            
            Log::info('Evidencia encontrada/creada: ' . ($evidencia ? 'SI' : 'NO'));
            if ($evidencia) {
                Log::info('Evidencia ID: ' . $evidencia->id);
            }
            // Comentado: Lógica de competencias y guías de aprendizaje
            // $guiaAprendizajeActual = $this->registroActividadesService->getGuiasAprendizaje($caracterizacion);
            // $actividades = $this->registroActividadesService->getActividades($caracterizacion);

            // Delegar al servicio - usar el ficha_id de la caracterizacion del instructor
            Log::info('Ficha ID a buscar en servicio: ' . $caracterizacion->ficha_id);
            $datosCaracterizacion = $this->asistenceQrService->obtenerDatosCaracterizacion(
                $caracterizacion->ficha_id, // Usar ficha_id en lugar de id
                Auth::user(),
                $asistencia?->id
            );

            if (!$datosCaracterizacion['fichaCaracterizacion']) {
                return redirect()->back()->with('error', 'Ficha de caracterización no encontrada.');
            }

            // Comentado: Obtener RAP actual
            // $rapActual = $caracterizacion->ficha->programaFormacion->competenciaActual()->rapActual();

            Log::info('Pasando a la vista - asistencia ID: ' . ($asistencia ? $asistencia->id : 'NULL'));
            Log::info('Pasando a la vista - asistencia está finalizada: ' . ($asistencia ? ($asistencia->is_finished ? 'SI' : 'NO') : 'SIN ASISTENCIA'));

            return view('qr_asistence.index', [
                'caracterizacion' => $caracterizacion,
                'fichaCaracterizacion' => $datosCaracterizacion['fichaCaracterizacion'],
                'aprendizPersonaConAsistencia' => $datosCaracterizacion['aprendices'],
                'horarioHoy' => $datosCaracterizacion['horarioHoy'],
                'asistencia' => $asistencia, // Cambiado de evidencia a asistencia
                'evidencia' => $evidencia, // Agregar evidencia a la vista
                // Comentado: Variables de competencias
                // 'guiaAprendizajeActual' => $guiaAprendizajeActual,
                // 'rapActual' => $rapActual,
                // 'actividades' => $actividades,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en caracterSelected - ERROR COMPLETO:');
            Log::error('Mensaje: ' . $e->getMessage());
            Log::error('Archivo: ' . $e->getFile());
            Log::error('Línea: ' . $e->getLine());
            Log::error('Trace: ' . $e->getTraceAsString());
            Log::error('Caracterizacion ID: ' . ($caracterizacion->id ?? 'NULL'));
            Log::error('Evidencia: ' . ($evidencia->id ?? 'NULL'));
            Log::error('=== FIN ERROR COMPLETO ===');
            return redirect()->back()->with('error', 'Error al cargar caracterización. Revisa el log para más detalles.');
        }
    }



    /**
     * Almacena la asistencia de los aprendices en la base de datos.
     *
     * @param \Illuminate\Http\Request $request La solicitud HTTP que contiene los datos de la asistencia.
     * @return \Illuminate\Http\RedirectResponse Redirige a la ruta 'qr_asistence.index' con un mensaje de éxito o error.
     */
    public function store(Request $request)
    {
        $data = $request->all();

        if(!$data){
            return back()->with('Error', 'No hay datos registrados.');
        }

        // Obtener la asistencia activa para esta ficha
        $asistenciaActiva = \App\Models\Asistencia::deFicha($data['caracterizacion_id'])
            ->activa()
            ->first();

        // Verificar si hay asistencia activa y si está finalizada
        if (!$asistenciaActiva) {
            return back()->with('error', 'No hay una sesión de asistencia activa para esta ficha.');
        }

        if ($asistenciaActiva->is_finished) {
            return back()->with('error', 'La asistencia ya fue finalizada. No se pueden registrar más ingresos.');
        }

        foreach($data['asistencia'] as $asistence){
            $asistenceData = json_decode($asistence, true);
            Log::info($asistenceData);

            // Crear registro de asistencia del aprendiz
            $asistenciaAprendiz = AsistenciaAprendiz::create([
                'asistencia_id' => $asistenciaActiva->id, // Usar asistencia_id en lugar de evidencia_id
                'instructor_ficha_id' => $data['caracterizacion_id'],
                'aprendiz_ficha_id' => $asistenceData['aprendiz_ficha_id'] ?? null,
                'hora_ingreso' => $asistenceData['hora_ingreso'],
                'user_create_id' => auth()->id(),
                'user_edit_id' => auth()->id(),
            ]);

            Log::info('Asistencia de aprendiz registrada:', [
                'asistencia_id' => $asistenciaActiva->id,
                'aprendiz_ficha_id' => $asistenciaAprendiz->aprendiz_ficha_id,
                'hora_ingreso' => $asistenciaAprendiz->hora_ingreso
            ]);
        }

        if (!empty($asistenciaAprendiz) || $asistenciaAprendiz !== null) {
            return back()->with('success', 'Asistencia registrada exitosamente.');
        } else {
            return back()->with('error', 'Error al registrar la asistencia.');
        }
    }



    /**
     * Obtiene la lista de asistencias web para una ficha y jornada específicas.
     *
     * @param string $ficha El identificador de la ficha.
     * @param string $jornada El identificador de la jornada.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View Redirige de vuelta con un mensaje de error o muestra la vista con la lista de asistencias.
     */
    public function getAsistenceWebList (string $ficha, string $jornada) {

        // Obtiene la hora y fecha actual
        $horaEjecucion = Carbon::now()->format('H:i:s');
        $fechaActual = Carbon::now()->format('Y-m-d');

        // Obtiene la jornada de formación basada en el identificador de jornada desde parametros_temas
        $obJornada = ParametroTema::whereHas('tema', function($q) {
            $q->where('name', 'LIKE', '%JORNADAS%');
        })->whereHas('parametro', function($query) use ($jornada) {
            $query->where('name', $jornada);
        })->with('parametro')->first();

        // Usar horarios por defecto si no se encuentra la jornada (las jornadas en parametros_temas no tienen hora_inicio/hora_fin)
        $hI = 8; // Hora inicio por defecto
        $mI = 0;
        $h2I = 12; // Hora fin por defecto
        $m2F = 0;

        // Obtiene las asistencias de los aprendices para la ficha y jornada especificadas en la fecha actual
        $asistencias = AsistenciaAprendiz::whereHas('caracterizacion', function ($query) use ($ficha, $jornada) {
            $query->whereHas('ficha', function ($query) use ($ficha) {
                $query->where('ficha', $ficha);
            })->whereHas('jornada', function ($query) use ($jornada) {
                $query->where('jornada', $jornada);
            });
        })->whereDate('created_at', $fechaActual)->get();

        // Si no se encontraron asistencias, redirige de vuelta con un mensaje de error
        if ($asistencias->isEmpty() || $asistencias === null) {
            return back()->with('error', 'No se encontraron asistencias para la ficha y jornada proporcionadas');
        }

        // Itera sobre las asistencias obtenidas
        foreach ($asistencias as $asistencia){

            // Formatea la hora y fecha de ingreso de la asistencia
            $hourEnter = Carbon::parse($asistencia->hora_ingreso)->format('H:i:s');
            $dateEnter =  carbon::parse($asistencia->created_at)->format('Y-m-d');

            // Valida si la hora de ejecución está dentro del rango de la jornada y si la fecha de ingreso es la actual
            if($this->validateHour($horaEjecucion, $jornada , $hI , $mI , $h2I , $m2F) == true  && $dateEnter == $fechaActual){
                return view('qr_asistence.showList', compact('asistencias', 'ficha'));
            }
        }

        // Si no se encontró ninguna asistencia válida, retornar error
        return back()->with('error', 'No se encontraron asistencias válidas para la ficha y jornada proporcionadas');
    }


    ///***** METODOS QUE PERMITEN OBTENER LA LISTA DE ASISTENCIA POR HORARIO Y JORNADA    **** */

    public function validateHour($ingreso, $jornada, $hora1, $min1, $hora2, $min2)
    {
        $horaInicio = Carbon::createFromTime($hora1, $min1 , 0);
        $horaFin = Carbon::createFromTime($hora2, $min2, 0);

        $horaIngreso = Carbon::parse($ingreso);

        if ($horaIngreso->between($horaInicio, $horaFin)) {
            return true;
        }

         // if ($horaIngreso->between($horaInicio, $horaFin) && $jornada === $morning ) {
        //     return true;
        // }


        return false;
    }


    /**
     * Verifica si la hora de ingreso está dentro del rango de la mañana y si la jornada es "Mañana".
     *
     * @param string $ingreso La hora de ingreso en formato de cadena.
     * @param string $jornada La jornada a verificar.
     * @return bool Retorna true si la hora de ingreso está entre las 06:00 y las 13:10 y la jornada es "Mañana", de lo contrario retorna false.
     */
    public function morning($ingreso, $jornada)
    {
        $horaInicio = Carbon::createFromTime(06, 00, 0);
        $horaFin = Carbon::createFromTime(13, 10, 0);
        $morning = 'Mañana';

        $horaIngreso = Carbon::parse($ingreso);

        if ($horaIngreso->between($horaInicio, $horaFin) && $jornada === $morning ) {
            return true;
        }

        return false;
    }

    /**
     * Verifica si la hora de ingreso está dentro del rango de la tarde.
     *
     * @param string $ingreso La hora de ingreso en formato de cadena.
     * @param string $jornada La jornada a verificar, debe ser 'Tarde'.
     * @return bool Retorna true si la hora de ingreso está entre las 13:00 y las 18:10 y la jornada es 'Tarde', de lo contrario retorna false.
     */
    public function afternoon ($ingreso, $jornada){
        $horaInicio = Carbon::createFromTime(13, 00, 0);
        $horaFin = Carbon::createFromTime(18, 10, 0);
        $morning = 'Tarde';

        $horaIngreso = Carbon::parse($ingreso);

        if ($horaIngreso->between($horaInicio, $horaFin) && $morning === $jornada) {
            return true;
        }

        return false;
    }

    /**
     * Verifica si una hora de ingreso corresponde a la jornada nocturna.
     *
     * @param string $ingreso La hora de ingreso en formato de cadena.
     * @param string $jornada El tipo de jornada (debe ser 'Noche' para que coincida).
     * @return bool Retorna true si la hora de ingreso está entre las 17:50 y las 23:10 y la jornada es 'Noche', de lo contrario retorna false.
     */
    public function night($ingreso, $jornada)
    {
        $horaInicio = Carbon::createFromTime(17, 50, 0);
        $horaFin = Carbon::createFromTime(23, 10, 0);
        $night = 'Noche';

        $horaIngreso = Carbon::parse($ingreso);

        if ($horaIngreso->between($horaInicio, $horaFin) && $jornada === $night) {
            return true;
        }

        return false;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeEvidencia(Request $request)
    {
        Log::info('=== DEBUG STORE EVIDENCIA ===');
        Log::info('Request data: ' . json_encode($request->all()));
        
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'caracterizacion_id' => 'required|integer',
                'ficha_id' => 'required|integer',
            ]);
            
            Log::info('Validación pasada');
            Log::info('Creando evidencia con nombre: ' . $request->nombre);
            
            // Verificar si ya existe una evidencia con el mismo nombre
            $nombreOriginal = $request->nombre;
            $nombreFinal = $nombreOriginal;
            $contador = 1;
            
            while (\App\Models\Evidencias::where('nombre', $nombreFinal)->exists()) {
                $nombreFinal = $nombreOriginal . ' ' . $contador;
                $contador++;
            }
            
            if ($nombreFinal !== $nombreOriginal) {
                Log::info('Nombre duplicado, usando: ' . $nombreFinal);
            }
            
            // Crear la evidencia sin dependencias de competencias
            $evidencia = \App\Models\Evidencias::create([
                'nombre' => $nombreFinal,
                'id_estado' => 1, // Estado activo por defecto
                'fecha_evidencia' => now(),
                'user_create_id' => auth()->id(),
            ]);
            
            Log::info('Evidencia creada con ID: ' . $evidencia->id);
            Log::info('Evidencia datos: ' . json_encode($evidencia->toArray()));
            
            $responseData = [
                'success' => true,
                'message' => 'Evidencia creada exitosamente',
                'evidencia_id' => $evidencia->id,
                'evidencia_nombre' => $evidencia->nombre
            ];
            
            Log::info('Respuesta JSON a enviar: ' . json_encode($responseData));
            
            return response()->json($responseData);

        } catch (\Exception $e) {
            Log::error('Error en storeEvidencia: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la evidencia: ' . $e->getMessage()
            ], 500);
        }
        
        Log::info('=== FIN DEBUG STORE EVIDENCIA ===');
    }

    /*** METODOS PARA REDIRIGIR A FORMULARIO DE ENTRADA Y SALIDA DE LA ASISTENCIA WEB */

    /**
     * Redirige al aprendiz a la vista de salida de asistencia.
     *
     * Este método busca un registro de asistencia de aprendiz basado en la identificación,
     * la hora de ingreso y la fecha proporcionadas. Si no se encuentra un registro que coincida
     * con los datos proporcionados, redirige de vuelta con un mensaje de error. Si se encuentra
     * un registro, redirige a la vista de nueva salida de asistencia con los datos de asistencia.
     *
     * @param string $identificacion El número de identificación del aprendiz.
     * @param string $ingreso La hora de ingreso del aprendiz.
     * @param string $fecha La fecha de la asistencia en formato 'Y-m-d'.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View Redirección con mensaje de error o vista de nueva salida de asistencia.
     */
    public function redirectAprenticeExit (string $identificacion , string $ingreso , string $fecha) {

        $fecha = Carbon::parse($fecha)->format('Y-m-d');
        $asistencia = AsistenciaAprendiz::where('numero_identificacion', $identificacion)
            ->where('hora_ingreso', $ingreso)
            ->whereDate('created_at', $fecha)
            ->first();


        if (!$asistencia) {
            return back()->with('error', 'No se encontró asistencia con los datos proporcionados.');
        }

        return view('qr_asistence.newExitAsistence', compact('asistencia'));
    }

    /**
     * Redirige a la vista de entrada de aprendiz con la asistencia correspondiente.
     *
     * @param string $identificacion Número de identificación del aprendiz.
     * @param string $ingreso Hora de ingreso del aprendiz.
     * @param string $fecha Fecha de la asistencia en formato 'Y-m-d'.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     *         Redirige de vuelta con un mensaje de error si no se encuentra la asistencia,
     *         o muestra la vista 'qr_asistence.newEntranceAsistence' con los datos de la asistencia.
     */
    public function redirectAprenticeEntrance (string $identificacion , string $ingreso , string $fecha) {

        $fecha = Carbon::parse($fecha)->format('Y-m-d');
        $asistencia = AsistenciaAprendiz::where('numero_identificacion', $identificacion)
            ->where('hora_ingreso', $ingreso)
            ->whereDate('created_at', $fecha)
            ->first();

        if (!$asistencia) {
            return back()->with('error', 'No se encontró asistencia con los datos proporcionados.');
        }

        return view('qr_asistence.newEntranceAsistence', compact('asistencia'));

    }


    /**** METODOS PARA SALIDDA DE FORMACIÓN Y ACTUALIZACION DE NOVEDADES DE ENTRADA Y SALIDA */

    /**
     * Actualiza la hora de salida de las asistencias de un aprendiz para una fecha específica.
     *
     * @param string $caracterizacion_id El ID de la caracterización del aprendiz.
     * @return \Illuminate\Http\RedirectResponse Redirige de vuelta con un mensaje de éxito o error.
     *
     * Este método busca las asistencias del aprendiz para la fecha actual y actualiza la hora de salida
     * con la hora actual. Si no se encuentran asistencias, redirige de vuelta con un mensaje de error.
     * Si se actualizan las asistencias correctamente, redirige de vuelta con un mensaje de éxito.
     */
    public function exitFormationAsistenceWeb(string $caracterizacion_id) {
        $fechaActual = Carbon::now()->format('Y-m-d');

        $asistencias = AsistenciaAprendiz::where('caracterizacion_id', $caracterizacion_id)
            ->whereDate('created_at', $fechaActual)
            ->get();

        if ($asistencias->isEmpty() || $asistencias === null) {
            return back()->with('error', 'No se encontraron asistencias para la ficha y jornada proporcionadas');
        }

        foreach ($asistencias as $asistencia) {
            $asistencia->update([
                'hora_salida' => Carbon::now()->format('H:i:s')
            ]);
        }

        return back()->with('success', 'Hora de salida actualizada exitosamente.');
    }


    /**
     * Actualiza la hora de salida y la novedad de salida de un registro de asistencia existente.
     *
     * @param \Illuminate\Http\Request $request La solicitud HTTP que contiene los datos necesarios.
     *
     * @return \Illuminate\Http\RedirectResponse Redirige de vuelta con un mensaje de éxito.
     *
     * @throws \Illuminate\Validation\ValidationException Si la validación de los datos falla.
     *
     * Validación de los datos de entrada:
     * - 'identificacion': Requerido, cadena de texto, máximo 255 caracteres.
     * - 'nombres': Requerido, cadena de texto, máximo 255 caracteres.
     * - 'apellidos': Requerido, cadena de texto, máximo 255 caracteres.
     * - 'novedad': Requerido, cadena de texto, máximo 255 caracteres.
     *
     * Este método busca un registro de asistencia del aprendiz basado en su número de identificación
     * y la fecha actual. Si se encuentra un registro, actualiza la hora de salida y la novedad de salida.
     */
    public function setNewExitAsistenceWeb(Request $request) {
        $data = $request->all();


        $request->validate([
            'identificacion' => 'required|string|max:255',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'novedad' => 'required|string|max:255',
        ]);

        $fechaEjecucion = Carbon::now()->format('Y-m-d');

        $asistencia = AsistenciaAprendiz::where('numero_identificacion', $data['identificacion'])
            ->whereDate('created_at', $fechaEjecucion)
            ->first();

        $asistencia->update([
            'hora_salida' =>  Carbon::now()->format('H:i:s'),
            'novedad_salida' => $data['novedad']
        ]);

        return back()->with('success', 'Novedad de salida actualizada exitosamente.');


    }


    /**
     * Establece una nueva novedad de entrada para la asistencia web.
     *
     * @param \Illuminate\Http\Request $request La solicitud HTTP que contiene los datos de la novedad de entrada.
     * @return \Illuminate\Http\RedirectResponse Redirige de vuelta con un mensaje de éxito.
     *
     * @throws \Illuminate\Validation\ValidationException Si la validación de los datos de la solicitud falla.
     *
     * Validación de la solicitud:
     * - 'identificacion': requerido, cadena de texto, máximo 255 caracteres.
     * - 'nombres': requerido, cadena de texto, máximo 255 caracteres.
     * - 'apellidos': requerido, cadena de texto, máximo 255 caracteres.
     * - 'novedad': requerido, cadena de texto, máximo 255 caracteres.
     *
     * Este método busca una entrada de asistencia para el aprendiz con el número de identificación proporcionado
     * y la fecha actual. Si se encuentra una entrada, actualiza el campo 'novedad_entrada' con la novedad proporcionada.
     */
    public function setNewEntranceAsistenceWeb(Request $request) {
        $data = $request->all();
        $request->validate([
            'identificacion' => 'required|string|max:255',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'novedad' => 'required|string|max:255',
        ]);

        $fechaEjecucion = Carbon::now()->format('Y-m-d');

        $asistencia = AsistenciaAprendiz::where('numero_identificacion', $data['identificacion'])
            ->whereDate('created_at', $fechaEjecucion)
            ->first();

        $asistencia->update([
            'novedad_entrada' => $data['novedad']
        ]);

        return back()->with('success', 'Novedad de entrada actualizada exitosamente.');
    }

    /**
     * Verifica si un número de documento existe como aprendiz en la ficha actual,
     * y si ya tiene asistencia de entrada registrada para el día actual.
     * Si no tiene, registra la asistencia de entrada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyDocument(Request $request)
    {
        Log::info('=== DEBUG VERIFY DOCUMENT QR ===');
        Log::info('Request data: ' . json_encode($request->all()));
        
        $request->validate([
            'numero_documento' => 'required|string',
            'ficha_id' => 'required|integer|exists:fichas_caracterizacion,id',
            'evidencia_id' => 'required|integer|exists:evidencias,id', // Asegúrate de que la evidencia_id sea requerida y válida
        ]);

        $numeroDocumento = $request->input('numero_documento');
        $fichaId = $request->input('ficha_id');
        $evidenciaId = $request->input('evidencia_id'); // Obtener el ID de la evidencia del request
        $fechaActual = Carbon::now()->format('Y-m-d');
        $horaIngreso = Carbon::now()->format('H:i:s');
        
        Log::info('Datos extraídos:');
        Log::info('Numero documento: ' . $numeroDocumento);
        Log::info('Ficha ID: ' . $fichaId);
        Log::info('Evidencia ID: ' . $evidenciaId);
        Log::info('Fecha actual: ' . $fechaActual);
        Log::info('Hora ingreso: ' . $horaIngreso);

        try {
            DB::beginTransaction();

            // 1. Obtener el ID del instructor actual (logueado)
            $user = Auth::user();
            if (!$user || !$user->persona || !$user->persona->instructor) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se pudo identificar al instructor actual.'
                ], 403);
            }
            $instructorId = $user->persona->instructor->id;

            // 2. Encontrar el instructor_ficha_id para la ficha y el instructor actual
            $instructorFicha = InstructorFichaCaracterizacion::where('instructor_id', $instructorId)
                ->where('ficha_id', $fichaId)
                ->first();

            if (!$instructorFicha) {
                DB::rollBack();
                return response()->json([
                    'status' => 'not_assigned_instructor',
                    'message' => 'El instructor no está asignado a esta ficha.'
                ], 403);
            }
            $instructorFichaId = $instructorFicha->id;

            // 3. Buscar la Persona por número de documento
            $persona = Persona::where('numero_documento', $numeroDocumento)->first();

            if (!$persona) {
                DB::rollBack();
                return response()->json([
                    'status' => 'not_found',
                    'message' => 'El aprendiz con documento ' . $numeroDocumento . ' no se encontró en el sistema.'
                ], 404);
            }

            // 4. Buscar el Aprendiz asociado a esa Persona
            $aprendiz = $persona->aprendiz;

            if (!$aprendiz) {
                DB::rollBack();
                return response()->json([
                    'status' => 'not_a_learner',
                    'message' => 'La persona encontrada no está registrada como aprendiz.'
                ], 404);
            }

            // 5. Verificar que el aprendiz pertenece a esta ficha
            Log::info('Validando pertenencia a ficha:');
            Log::info('Aprendiz ficha_caracterizacion_id: ' . $aprendiz->ficha_caracterizacion_id);
            Log::info('Ficha ID requerida: ' . $fichaId);
            
            // Comentado: Validación de ficha - permitir asistencia sin importar la ficha del aprendiz
            // if ($aprendiz->ficha_caracterizacion_id != $fichaId) {
            //     Log::error('El aprendiz no pertenece a esta ficha');
            //     DB::rollBack();
            //     return response()->json([
            //         'status' => 'not_in_ficha',
            //         'message' => 'El aprendiz ' . $persona->getNombreCompletoAttribute() . ' no pertenece a esta ficha.'
            //     ], 404);
            // }
            
            Log::info('Validación de ficha omitida - permitiendo registro de asistencia');
            
            // Buscar el aprendiz correcto que pertenece a esta ficha usando el mismo documento
            $aprendizCorrecto = \App\Models\Aprendiz::whereHas('persona', function($query) use ($numeroDocumento) {
                $query->where('numero_documento', $numeroDocumento);
            })->where('ficha_caracterizacion_id', $fichaId)->first();
            
            if ($aprendizCorrecto) {
                Log::info('Aprendiz correcto encontrado en esta ficha: ID=' . $aprendizCorrecto->id);
                $aprendizFichaId = $aprendizCorrecto->id; // Usar el ID del aprendiz correcto
            } else {
                Log::info('Usando aprendiz original (no se encontró en esta ficha)');
                $aprendizFichaId = $aprendiz->id; // Usar el aprendiz original como fallback
            }

            // 6. Obtener la sesión activa (Asistencia) para esta ficha y usarla como origen de verdad
            $asistenciaActiva = \App\Models\Asistencia::deFicha($fichaId)
                ->activa()
                ->first();

            if (!$asistenciaActiva) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'No hay una sesión de asistencia activa para esta ficha.'
                ], 409);
            }

            if ($asistenciaActiva->is_finished) {
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'La sesión de asistencia ya fue finalizada. No se pueden registrar más ingresos o salidas.'
                ], 409);
            }

            // 7. Verificar si el aprendiz ya tiene una asistencia de entrada para hoy en ESTA sesión
            $asistenciaExistente = AsistenciaAprendiz::where('asistencia_id', $asistenciaActiva->id)
                ->where('aprendiz_ficha_id', $aprendizFichaId)
                ->where('instructor_ficha_id', $instructorFichaId)
                ->whereDate('created_at', $fechaActual)
                ->whereNotNull('hora_ingreso')
                ->first();

            if ($asistenciaExistente) {
                // Si ya tiene asistencia de entrada, verificar si ya tiene hora de salida
                if ($asistenciaExistente->hora_salida === null) {
                    $asistenciaExistente->update([
                        'hora_salida' => $horaIngreso, // Usamos $horaIngreso para la hora actual
                    ]);

                    // Disparar evento de nueva asistencia registrada (salida)
                    event(new NuevaAsistenciaRegistrada([
                        'id' => $asistenciaExistente->id,
                        'aprendiz' => $persona->getNombreCompletoAttribute(),
                        'estado' => 'salida',
                        'timestamp' => now()->toISOString(),
                    ]));

                    DB::commit();
                    return response()->json([
                        'status' => 'exit_registered',
                        'message' => 'Asistencia de salida registrada para ' . $persona->getNombreCompletoAttribute() . '.',
                        'hora_ingreso' => Carbon::parse($asistenciaExistente->hora_ingreso)->format('h:i A'),
                        'hora_salida' => Carbon::parse($asistenciaExistente->hora_salida)->format('h:i A'), // Enviar la hora de salida
                        'aprendiz_data' => [
                            'numero_documento' => $persona->numero_documento,
                        ]
                    ], 200);
                } else {
                    // Si ya tiene entrada y salida, indicar que ya completó la asistencia
                    DB::rollBack(); // Aunque no hay cambios, es buena práctica si la transacción está activa
                    return response()->json([
                        'status' => 'attendance_complete',
                        'message' => 'El aprendiz ' . $persona->getNombreCompletoAttribute() . ' ya completó su asistencia hoy.',
                        'hora_ingreso' => Carbon::parse($asistenciaExistente->hora_ingreso)->format('h:i A'),
                        'hora_salida' => Carbon::parse($asistenciaExistente->hora_salida)->format('h:i A'),
                        'aprendiz_data' => [
                            'numero_documento' => $persona->numero_documento,
                        ]
                    ], 200);
                }
            }

            // 8. Si no tiene asistencia de entrada, registrarla
            Log::info('Creando nuevo registro de asistencia...');
            Log::info('Asistencia activa ID a usar: ' . $asistenciaActiva->id);
            Log::info('Instructor Ficha ID: ' . $instructorFichaId);
            Log::info('Aprendiz Ficha ID: ' . $aprendizFichaId);
            
            $asistencia = AsistenciaAprendiz::create([
                'asistencia_id' => $asistenciaActiva->id,
                'instructor_ficha_id' => $instructorFichaId,
                'aprendiz_ficha_id' => $aprendizFichaId,
                'hora_ingreso' => $horaIngreso,
                'hora_salida' => null,
            ]);
            
            Log::info('Asistencia creada con ID: ' . $asistencia->id);
            Log::info('Asistencia ID guardado en asistencia_aprendices: ' . $asistencia->asistencia_id);
            Log::info('Hora de ingreso guardada: ' . $asistencia->hora_ingreso);

            // Disparar evento de WebSocket para notificar el escaneo de QR
            event(new QrScanned([
                'numero_documento' => $numeroDocumento,
                'aprendiz_nombre' => $persona->getNombreCompletoAttribute(),
                'ficha_id' => $fichaId,
                'hora_ingreso' => $horaIngreso,
                'tipo' => 'entrada',
                'instructor_id' => $instructorId,
            ]));

            // Disparar evento de nueva asistencia registrada
            event(new NuevaAsistenciaRegistrada([
                'id' => $asistencia->id,
                'aprendiz' => $persona->getNombreCompletoAttribute(),
                'estado' => 'entrada',
                'timestamp' => now()->toISOString(),
            ]));

            DB::commit();

            $responseData = [
                'status' => 'registered',
                'message' => 'Asistencia de entrada registrada para ' . $persona->getNombreCompletoAttribute() . '.',
                'hora_ingreso' => Carbon::parse($asistencia->hora_ingreso)->format('h:i A'),
                'aprendiz_data' => [ // Envía los datos de la persona para actualizar la fila en la vista
                    'numero_documento' => $persona->numero_documento,
                    'primer_nombre' => $persona->primer_nombre,
                    'segundo_nombre' => $persona->segundo_nombre,
                    'primer_apellido' => $persona->primer_apellido,
                    'segundo_apellido' => $persona->segundo_apellido,
                ]
            ];
            
            Log::info('Respuesta JSON enviada: ' . json_encode($responseData));
            Log::info('=== FIN DEBUG VERIFY DOCUMENT ===');
            
            return response()->json($responseData, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al verificar o registrar asistencia QR: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error en el servidor al procesar la asistencia.'
            ], 500);
        }
    }

    /**
     * Obtiene la próxima clase para una ficha específica
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProximaClase(Request $request)
    {
        $request->validate([
            'ficha_id' => 'required|integer|exists:fichas_caracterizacion,id',
        ]);

        try {
            $fichaId = $request->input('ficha_id');

            // Obtener el instructor actual
            $user = Auth::user();
            if (!$user || !$user->persona || !$user->persona->instructor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se pudo identificar al instructor actual.'
                ], 403);
            }

            $instructorId = $user->persona->instructor->id;

            // Buscar la relación instructor-ficha
            $instructorFicha = InstructorFichaCaracterizacion::where('instructor_id', $instructorId)
                ->where('ficha_id', $fichaId)
                ->first();

            if (!$instructorFicha) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El instructor no está asignado a esta ficha.'
                ], 404);
            }

            // Obtener la próxima clase
            $proximaClase = $instructorFicha->obtenerProximaClase();
            $claseActual = $instructorFicha->obtenerClaseActual();

            if (!$proximaClase) {
                return response()->json([
                    'status' => 'no_classes',
                    'message' => 'No hay clases programadas para esta ficha.',
                    'data' => null
                ], 404);
            }

            // Formatear las horas para mejor presentación
            $proximaClase['hora_inicio_formatted'] = Carbon::parse($proximaClase['hora_inicio'])->format('h:i A');
            $proximaClase['hora_fin_formatted'] = Carbon::parse($proximaClase['hora_fin'])->format('h:i A');

            return response()->json([
                'status' => 'success',
                'message' => 'Próxima clase obtenida exitosamente.',
                'data' => [
                    'proxima_clase' => $proximaClase,
                    'clase_actual' => $claseActual
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener próxima clase: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al obtener la próxima clase.'
            ], 500);
        }
    }

    /**
     * Obtiene la próxima clase para una ficha específica (versión web)
     *
     * @param int $fichaId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProximaClaseWeb($fichaId)
    {
        try {
            // Obtener el instructor actual
            $user = Auth::user();
            if (!$user || !$user->persona || !$user->persona->instructor) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No se pudo identificar al instructor actual.'
                ], 403);
            }

            $instructorId = $user->persona->instructor->id;

            // Buscar la relación instructor-ficha
            $instructorFicha = InstructorFichaCaracterizacion::where('instructor_id', $instructorId)
                ->where('ficha_id', $fichaId)
                ->first();

            if (!$instructorFicha) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El instructor no está asignado a esta ficha.'
                ], 404);
            }

            // Obtener la próxima clase
            $proximaClase = $instructorFicha->obtenerProximaClase();
            $claseActual = $instructorFicha->obtenerClaseActual();

            return response()->json([
                'status' => 'success',
                'proxima_clase' => $proximaClase,
                'clase_actual' => $claseActual
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error al obtener próxima clase web: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error al obtener la próxima clase.'
            ], 500);
        }
    }

        /**
     * Agrega una nueva actividad a la ficha de caracterización.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function agregar_actividad(Request $request)
    {
        // Validar los datos recibidos
        $request->validate([
            'ficha_id' => 'required|exists:ficha_caracterizacions,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha' => 'required|date',
        ]);

        try {
            // Crear la actividad (suponiendo que existe el modelo Actividad y la relación)
            $actividad = new \App\Models\RegistroActividades();
            $actividad->ficha_id = $request->input('ficha_id');
            $actividad->titulo = $request->input('titulo');
            $actividad->descripcion = $request->input('descripcion');
            $actividad->fecha = $request->input('fecha');
            $actividad->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Actividad agregada correctamente.',
                'actividad' => $actividad
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error al agregar actividad: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo agregar la actividad.'
            ], 500);
        }
    }

    /**
     * Guarda un mensaje de alerta en la sesión de Laravel
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setSessionAlert(Request $request)
    {
        try {
            $request->validate([
                'key' => 'required|string|in:success,error,warning,info',
                'message' => 'required|string|max:255',
            ]);

            $key = $request->input('key');
            $message = $request->input('message');

            // Guardar en sesión
            session()->flash($key, $message);

            return response()->json([
                'status' => 'success',
                'message' => 'Alerta guardada en sesión'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al guardar alerta en sesión: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al guardar alerta en sesión'
            ], 500);
        }
    }

    /**
     * Registra asistencia para múltiples aprendices seleccionados
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registrarAsistenciaSeleccionados(Request $request)
    {
        try {
            $request->validate([
                'ficha_id' => 'required|integer',
                'caracterizacion_id' => 'required|integer',
                'asistencia_id' => 'required|integer',
                'aprendices_seleccionados' => 'nullable|array',
                'documento_manual' => 'nullable|string|max:20',
            ]);

            $fichaId = $request->input('ficha_id');
            $caracterizacionId = $request->input('caracterizacion_id');
            $asistenciaId = $request->input('asistencia_id');
            $aprendicesSeleccionados = $request->input('aprendices_seleccionados', []);
            $documentoManual = $request->input('documento_manual');

            Log::info('=== REGISTRAR ASISTENCIA SELECCIONADOS ===');
            Log::info('Ficha ID: ' . $fichaId);
            Log::info('Caracterización ID: ' . $caracterizacionId);
            Log::info('Asistencia ID: ' . $asistenciaId);
            Log::info('Aprendices seleccionados: ' . json_encode($aprendicesSeleccionados));
            Log::info('Documento manual: ' . $documentoManual);

            // Obtener la asistencia activa
            $asistencia = \App\Models\Asistencia::find($asistenciaId);
            if (!$asistencia) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sesión de asistencia no encontrada.'
                ], 404);
            }

            $registrosExitosos = 0;
            $errores = [];
            $aprendicesActualizados = [];

            // Procesar aprendices seleccionados con checkbox
            foreach ($aprendicesSeleccionados as $aprendizData) {
                $aprendizId = $aprendizData['aprendiz_id'];
                
                try {
                    $aprendiz = \App\Models\Aprendiz::find($aprendizId);
                    if (!$aprendiz) {
                        $errores[] = "Aprendiz ID {$aprendizId} no encontrado";
                        continue;
                    }

                    // Verificar si ya tiene asistencia hoy
                    $asistenciaExistente = \App\Models\AsistenciaAprendiz::where('asistencia_id', $asistenciaId)
                        ->where('aprendiz_ficha_id', $aprendizId)
                        ->first();

                    if ($asistenciaExistente) {
                        if ($asistenciaExistente->hora_salida) {
                            // Ya tiene entrada y salida, mostrar error
                            $errores[] = "El aprendiz {$aprendiz->persona->getNombreCompletoAttribute()} ya tiene asistencia completa (entrada y salida) registrada";
                            continue;
                        } else {
                            // Tiene entrada pero no salida, registrar salida
                            $asistenciaExistente->hora_salida = now();
                            $asistenciaExistente->save(); // Eliminar user_edit_id que no existe
                            
                            $registrosExitosos++;
                            Log::info('Salida registrada para aprendiz ID: ' . $aprendizId);
                            
                            // Agregar a lista de actualizados
                            $aprendicesActualizados[] = [
                                'documento' => $aprendiz->persona->numero_documento,
                                'hora_salida' => $asistenciaExistente->hora_salida->format('h:i A')
                            ];
                        }
                    } else {
                        // No tiene asistencia, crear registro de entrada
                        $nuevaAsistencia = \App\Models\AsistenciaAprendiz::create([
                            'asistencia_id' => $asistenciaId,
                            'instructor_ficha_id' => $asistencia->instructor_ficha_id,
                            'aprendiz_ficha_id' => $aprendizId,
                            'hora_ingreso' => now(),
                            'user_create_id' => auth()->id(),
                        ]);

                        $registrosExitosos++;
                        Log::info('Entrada registrada para aprendiz ID: ' . $aprendizId);
                        
                        // Agregar a lista de actualizados
                        $aprendicesActualizados[] = [
                            'documento' => $aprendiz->persona->numero_documento,
                            'hora_ingreso' => $nuevaAsistencia->hora_ingreso->format('h:i A')
                        ];
                    }

                } catch (\Exception $e) {
                    Log::error('Error registrando asistencia para aprendiz ID ' . $aprendizId . ': ' . $e->getMessage());
                    $errores[] = "Error al registrar asistencia para aprendiz ID {$aprendizId}";
                }
            }

            // Procesar documento manual si se ingresó
            if ($documentoManual) {
                try {
                    // Buscar aprendiz por documento
                    $persona = \App\Models\Persona::where('numero_documento', $documentoManual)->first();
                    if ($persona) {
                        $aprendiz = \App\Models\Aprendiz::where('persona_id', $persona->id)
                            ->where('ficha_caracterizacion_id', $fichaId)
                            ->first();

                        if ($aprendiz) {
                            // Verificar si ya tiene asistencia
                            $asistenciaExistente = \App\Models\AsistenciaAprendiz::where('asistencia_id', $asistenciaId)
                                ->where('aprendiz_ficha_id', $aprendiz->id)
                                ->first();

                            if ($asistenciaExistente) {
                                if ($asistenciaExistente->hora_salida) {
                                    // Ya tiene entrada y salida, mostrar error
                                    $errores[] = "El aprendiz con documento {$documentoManual} ya tiene asistencia completa (entrada y salida) registrada";
                                } else {
                                    // Tiene entrada pero no salida, registrar salida
                                    $asistenciaExistente->hora_salida = now();
                                    $asistenciaExistente->save(); // Eliminar user_edit_id que no existe
                                    
                                    $registrosExitosos++;
                                    Log::info('Salida manual registrada para documento: ' . $documentoManual);
                                }
                            } else {
                                // No tiene asistencia, crear registro de entrada
                                \App\Models\AsistenciaAprendiz::create([
                                    'asistencia_id' => $asistenciaId,
                                    'instructor_ficha_id' => $asistencia->instructor_ficha_id,
                                    'aprendiz_ficha_id' => $aprendiz->id,
                                    'hora_ingreso' => now(),
                                    'user_create_id' => auth()->id(),
                                ]);

                                $registrosExitosos++;
                                Log::info('Entrada manual registrada para documento: ' . $documentoManual);
                            }
                        } else {
                            $errores[] = "No se encontró aprendiz con documento {$documentoManual} en esta ficha";
                        }
                    } else {
                        $errores[] = "No se encontró persona con documento {$documentoManual}";
                    }
                } catch (\Exception $e) {
                    Log::error('Error registrando asistencia manual para documento ' . $documentoManual . ': ' . $e->getMessage());
                    $errores[] = "Error al registrar asistencia manual para documento {$documentoManual}";
                }
            }

            $message = "Se registraron {$registrosExitosos} asistencias correctamente.";
            if (!empty($errores)) {
                $message .= " Errores: " . implode(', ', $errores);
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                'registros_exitosos' => $registrosExitosos,
                'errores' => $errores,
                'aprendices_actualizados' => $aprendicesActualizados
            ]);

        } catch (\Exception $e) {
            Log::error('Error general en registrarAsistenciaSeleccionados: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finaliza la asistencia del día, genera PDF y bloquea hasta el día siguiente
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function finalizar_asistencia(Request $request)
    {
        try {
            $request->validate([
                'ficha_id' => 'required|integer',
                'caracterizacion_id' => 'required|integer',
                'asistencia_id' => 'required|integer',
                'observaciones' => 'nullable|string',
                'observaciones_aprendices' => 'nullable|array',
            ]);

            $fichaId = $request->input('ficha_id');
            $caracterizacionId = $request->input('caracterizacion_id');
            $asistenciaId = $request->input('asistencia_id');
            $observaciones = $request->input('observaciones');
            $observacionesAprendices = $request->input('observaciones_aprendices', []);
            
            Log::info('=== FINALIZANDO ASISTENCIA ===');
            Log::info('Ficha ID: ' . $fichaId);
            Log::info('Caracterización ID: ' . $caracterizacionId);
            Log::info('Asistencia ID: ' . $asistenciaId);
            Log::info('Observaciones: ' . $observaciones);
            Log::info('Observaciones aprendices: ' . json_encode($observacionesAprendices));
            
            // Obtener la asistencia
            $asistencia = \App\Models\Asistencia::find($asistenciaId);
            
            if (!$asistencia) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Asistencia no encontrada.'
                ], 404);
            }
            
            // Guardar observaciones generales antes de finalizar
            if ($observaciones !== null) {
                $asistencia->observaciones = $observaciones;
                $asistencia->save();
            }
            
            // Guardar observaciones de aprendices
            foreach ($observacionesAprendices as $aprendizId => $obsText) {
                $aprendiz = \App\Models\Aprendiz::find($aprendizId);
                if ($aprendiz) {
                    // Buscar si ya existe registro de asistencia para este aprendiz
                    $asistenciaAprendiz = \App\Models\AsistenciaAprendiz::where('asistencia_id', $asistenciaId)
                        ->where('aprendiz_ficha_id', $aprendizId)
                        ->first();
                    
                    if ($asistenciaAprendiz) {
                        // Actualizar observaciones si existe
                        $asistenciaAprendiz->observaciones = $obsText;
                        $asistenciaAprendiz->save();
                    } else {
                        // Crear nuevo registro si no existe
                        \App\Models\AsistenciaAprendiz::create([
                            'asistencia_id' => $asistenciaId,
                            'instructor_ficha_id' => $asistencia->instructor_ficha_id,
                            'aprendiz_ficha_id' => $aprendizId,
                            'hora_ingreso' => null,
                            'observaciones' => $obsText,
                            'user_create_id' => auth()->id(),
                        ]);
                    }
                    
                    Log::info('Guardadas observaciones para aprendiz ID: ' . $aprendizId . ' - ' . $obsText);
                }
            }
            
            // Finalizar la asistencia
            $asistencia->finalizar();
            
            // Obtener datos de la ficha
            $fichaCaracterizacion = \App\Models\FichaCaracterizacion::with([
                'programaFormacion',
                'ambiente.piso.bloque.sede',
                'jornadaFormacion',
                'modalidadFormacion'
            ])->find($fichaId);
            
            if (!$fichaCaracterizacion) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ficha no encontrada.'
                ], 404);
            }
            
            // Obtener instructor
            $caracterizacion = \App\Models\InstructorFichaCaracterizacion::with([
                'instructor.persona'
            ])->find($caracterizacionId);
            
            // Obtener evidencia desde la asistencia
            $evidencia = $asistencia->evidencia;
            
            // Obtener todos los aprendices de la ficha
            $todosLosAprendices = \App\Models\Aprendiz::with('persona')
                ->where('ficha_caracterizacion_id', $fichaId)
                ->get();
            
            // Obtener aprendices con asistencia de esta sesión (usando asistencia_id)
            $aprendicesConAsistencia = \App\Models\AsistenciaAprendiz::with('aprendiz.persona')
                ->where('asistencia_id', $asistenciaId) // Cambiado de evidencia_id a asistencia_id
                ->get();
            
            Log::info('Aprendices con asistencia encontrados: ' . $aprendicesConAsistencia->count());
            foreach ($aprendicesConAsistencia as $asistencia) {
                Log::info('Asistencia ID: ' . $asistencia->id . ', Aprendiz ID: ' . $asistencia->aprendiz_id . ', Aprendiz Ficha ID: ' . $asistencia->aprendiz_ficha_id);
            }
            
            // Separar aprendices que asistieron y los que no
            $aprendicesQueAsistieron = $aprendicesConAsistencia->pluck('aprendiz_ficha_id')->toArray();
            Log::info('IDs de aprendices que asistieron (usando aprendiz_ficha_id): ' . json_encode($aprendicesQueAsistieron));
            
            Log::info('Todos los aprendices de la ficha: ' . $todosLosAprendices->count());
            foreach ($todosLosAprendices as $aprendiz) {
                Log::info('Aprendiz ID: ' . $aprendiz->id . ', Nombre: ' . $aprendiz->persona->getNombreCompletoAttribute());
            }
            
            $asistieron = $todosLosAprendices->filter(function($aprendiz) use ($aprendicesQueAsistieron) {
                $asistio = in_array($aprendiz->id, $aprendicesQueAsistieron);
                Log::info('Aprendiz ' . $aprendiz->id . ' (' . $aprendiz->persona->getNombreCompletoAttribute() . ') asistió: ' . ($asistio ? 'SÍ' : 'NO'));
                return $asistio;
            });
            
            $noAsistieron = $todosLosAprendices->filter(function($aprendiz) use ($aprendicesQueAsistieron) {
                return !in_array($aprendiz->id, $aprendicesQueAsistieron);
            });
            
            Log::info('Total que asistieron (filtrado): ' . $asistieron->count());
            Log::info('Total que no asistieron (filtrado): ' . $noAsistieron->count());
            
            // Marcar la evidencia como finalizada
            $evidencia->update(['id_estado' => 27]); // Estado finalizado
            
            // Generar PDF
            $pdfData = $this->generarPdfAsistencia(
                $fichaCaracterizacion,
                $caracterizacion,
                $evidencia,
                $asistieron,
                $noAsistieron,
                $todosLosAprendices,
                $asistenciaId
            );
            
            Log::info('PDF generado correctamente');
            Log::info('Total asistieron: ' . $asistieron->count());
            Log::info('Total no asistieron: ' . $noAsistieron->count());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Asistencia finalizada correctamente',
                'pdf_url' => $pdfData['url'],
                'asistieron_count' => $asistieron->count(),
                'no_asistieron_count' => $noAsistieron->count(),
                'redirect_url' => route('asistence.web')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al finalizar asistencia: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al finalizar la asistencia: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Genera PDF con el reporte de asistencia
     */
    private function generarPdfAsistencia($fichaCaracterizacion, $caracterizacion, $evidencia, $asistieron, $noAsistieron, $todosLosAprendices, int $asistenciaId)
    {
        try {
            // Generar nombre de archivo
            $nombreArchivo = 'asistencia_' . $fichaCaracterizacion->ficha . '_' . date('Y-m-d_H-i-s') . '.pdf';
            $rutaArchivo = 'asistencia_pdfs/' . $nombreArchivo;
            
            // Crear directorio si no existe
            $directorio = public_path('asistencia_pdfs');
            if (!file_exists($directorio)) {
                mkdir($directorio, 0755, true);
            }
            
            // Generar PDF (usando DOMPDF)
            $pdf = \Barryvdh\DomPDF\Facade\PDF::loadView('pdf.asistencia_reporte', [
                'fichaCaracterizacion' => $fichaCaracterizacion,
                'caracterizacion' => $caracterizacion,
                'evidencia' => $evidencia,
                'asistenciaId' => $asistenciaId,
                'asistieron' => $asistieron,
                'noAsistieron' => $noAsistieron,
                'todosLosAprendices' => $todosLosAprendices,
                'fecha' => now()->format('d/m/Y'),
                'hora' => now()->format('h:i A')
            ]);
            
            // Guardar PDF
            $pdf->save($rutaArchivo);
            
            return [
                'url' => asset('asistencia_pdfs/' . $nombreArchivo),
                'filename' => $nombreArchivo
            ];
            
        } catch (\Exception $e) {
            Log::error('Error al generar PDF: ' . $e->getMessage());
            throw $e;
        }
    }

    public function terminar_actividad(Request $request)
    {
        try {
            Evidencias::terminarActividad($request->input('evidencia_id'));
            $caracterizacion = InstructorFichaCaracterizacion::findOrFail($request->input('caracterizacion'));
            return redirect()->route('registro-actividades.index', $caracterizacion)->with('success', 'Actividad terminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al terminar actividad: ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo terminar la actividad.');
        }
    }
}
