<?php

declare(strict_types=1);

namespace App\Services\Inventario\Interfaces;

use App\Models\Inventario\Producto;
use Illuminate\Http\UploadedFile;

interface ImageServiceInterface
{
    public function procesarImagen(?UploadedFile $imagen): string;
    public function procesarImagenParaActualizacion(?UploadedFile $imagen, Producto $producto): string;
    public function eliminarImagenSiExiste(Producto $producto): void;
}


