<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Verificando tema ESTADOS (ID: 1) y sus parámetros...\n\n";

// 1. Verificar el tema ESTADOS (ID: 1)
$tema = \App\Models\Tema::find(1);

if (!$tema) {
    echo "ERROR: No se encontró el tema con ID 1\n";
    exit(1);
}

echo "Tema encontrado: {$tema->name} (ID: {$tema->id})\n";

// 2. Verificar todos los parámetros asociados
$parametros = $tema->parametros()->get();
    
echo "Total parámetros asociados: {$parametros->count()}\n\n";

if ($parametros->count() > 0) {
    echo "Lista de parámetros:\n";
    foreach ($parametros as $parametro) {
        echo "- {$parametro->name} (ID: {$parametro->id})\n";
        
        // Obtener el ParametroTema correspondiente
        $parametroTema = \App\Models\ParametroTema::where('tema_id', $tema->id)
            ->where('parametro_id', $parametro->id)
            ->first();
        
        if ($parametroTema) {
            echo "  ParametroTema ID: {$parametroTema->id}, Status: {$parametroTema->status}\n";
        }
    }
}

echo "\n---\n\n";

// 3. Buscar específicamente los estados que necesitamos
$estadosNecesarios = ['Sin Oferta', 'Con Oferta', 'Cupos Llenos'];

echo "Buscando estados específicos:\n";
foreach ($estadosNecesarios as $estadoNombre) {
    $parametro = \App\Models\Parametro::where('name', $estadoNombre)->first();
    
    if ($parametro) {
        echo "- {$estadoNombre}: Encontrado (ID: {$parametro->id})\n";
        
        // Verificar si está asociado al tema ESTADOS
        $parametroTema = \App\Models\ParametroTema::where('tema_id', 1)
            ->where('parametro_id', $parametro->id)
            ->first();
        
        if ($parametroTema) {
            echo "  Asociado al tema ESTADOS: Sí (ParametroTema ID: {$parametroTema->id})\n";
        } else {
            echo "  Asociado al tema ESTADOS: No\n";
            
            // Verificar si está asociado a otro tema
            $otrosTemas = \App\Models\ParametroTema::where('parametro_id', $parametro->id)->get();
            if ($otrosTemas->count() > 0) {
                foreach ($otrosTemas as $otroTema) {
                    $temaInfo = \App\Models\Tema::find($otroTema->tema_id);
                    echo "  Asociado al tema: {$temaInfo->name} (ID: {$temaInfo->id})\n";
                }
            }
        }
    } else {
        echo "- {$estadoNombre}: No encontrado\n";
    }
}

echo "\n---\n\n";

// 4. Verificar si hay algún tema específico para programas complementarios
echo "Buscando temas relacionados con programas complementarios:\n";
$temasComplementarios = \App\Models\Tema::where('name', 'like', '%COMPLEMENTARIO%')->get();

if ($temasComplementarios->count() > 0) {
    foreach ($temasComplementarios as $temaComp) {
        echo "- {$temaComp->name} (ID: {$temaComp->id})\n";
    }
} else {
    echo "No hay temas con 'COMPLEMENTARIO' en el nombre\n";
}

echo "\nScript completado.\n";
