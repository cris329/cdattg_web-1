<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Verificando datos de estados de programas complementarios...\n\n";

// 1. Verificar el tema ESTADO_PROGRAMA_COMPLEMENTARIO
$tema = \App\Models\Tema::where('name', 'ESTADO_PROGRAMA_COMPLEMENTARIO')->first();

if (!$tema) {
    echo "ERROR: No se encontró el tema 'ESTADO_PROGRAMA_COMPLEMENTARIO'\n";
    echo "Esto explica por qué los estados aparecen como 'Desconocido'\n";
    
    // Verificar si hay algún tema similar
    $temasSimilares = \App\Models\Tema::where('name', 'like', '%ESTADO%')->orWhere('name', 'like', '%COMPLEMENTARIO%')->get();
    if ($temasSimilares->count() > 0) {
        echo "\nTemas similares encontrados:\n";
        foreach ($temasSimilares as $temaSim) {
            echo "- {$temaSim->name} (ID: {$temaSim->id})\n";
        }
    }
} else {
    echo "Tema encontrado: {$tema->name} (ID: {$tema->id})\n";
    
    // 2. Verificar parámetros asociados
    $parametros = $tema->parametros()->wherePivot('status', 1)->get();
    
    echo "Parámetros asociados: {$parametros->count()}\n";
    
    if ($parametros->count() > 0) {
        foreach ($parametros as $parametro) {
            echo "- {$parametro->name} (ID: {$parametro->id})\n";
            
            // Obtener el ParametroTema correspondiente
            $parametroTema = \App\Models\ParametroTema::where('tema_id', $tema->id)
                ->where('parametro_id', $parametro->id)
                ->first();
            
            if ($parametroTema) {
                echo "  ParametroTema ID: {$parametroTema->id}\n";
            }
        }
    } else {
        echo "ERROR: El tema no tiene parámetros asociados\n";
    }
}

echo "\n---\n\n";

// 3. Verificar algunos programas complementarios
echo "Verificando programas complementarios...\n";
$programas = \App\Models\Complementarios\ComplementarioOfertado::limit(5)->get();

if ($programas->count() > 0) {
    foreach ($programas as $programa) {
        echo "\nPrograma: {$programa->nombre} (ID: {$programa->id})\n";
        echo "Estado ID en BD: " . ($programa->estado_id ?? 'NULL') . "\n";
        echo "Estado (accessor): {$programa->estado}\n";
        echo "Estado Label (accessor): {$programa->estado_label}\n";
        
        // Intentar cargar la relación
        if ($programa->estado_id) {
            $estadoRel = $programa->estado()->with('parametro')->first();
            if ($estadoRel && $estadoRel->parametro) {
                echo "Estado desde relación: {$estadoRel->parametro->name}\n";
            } else {
                echo "ERROR: No se pudo cargar la relación estado->parametro\n";
            }
        }
    }
} else {
    echo "No hay programas complementarios en la base de datos\n";
}

echo "\n---\n\n";

// 4. Verificar el método getEstadoIdByLegacyValue
echo "Probando conversión de estados legacy...\n";
$repo = new \App\Repositories\Complementarios\ComplementarioOfertadoRepository();

foreach ([0, 1, 2] as $estadoLegacy) {
    $estadoId = $repo->getEstadoIdByLegacyValue($estadoLegacy);
    echo "Legacy {$estadoLegacy} -> Estado ID: " . ($estadoId ?? 'NULL') . "\n";
}

echo "\nScript completado.\n";
