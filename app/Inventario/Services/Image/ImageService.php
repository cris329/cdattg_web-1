<?php

declare(strict_types=1);

namespace App\Inventario\Services\Image;

use App\Inventario\Interfaces\Services\ImageServiceInterface;
use App\Models\Inventario\Producto;
use Illuminate\Http\UploadedFile;

class ImageService implements ImageServiceInterface
{
    /**
     * Obtiene la imagen por defecto desde configuración
     *
     * @return string
     */
    private function getDefaultImage(): string
    {
        return config('inventario.imagenes.default', 'img/inventario/producto-default.png');
    }

    /**
     * Obtiene el directorio de imágenes desde configuración
     *
     * @return string
     */
    private function getImageDirectory(): string
    {
        return config('inventario.imagenes.directorio', 'imagenes_productos');
    }

    public function procesarImagen(?UploadedFile $imagen): string
    {
        if (!$imagen || !$imagen->isValid()) {
            return $this->getDefaultImage();
        }

        $directory = $this->getImageDirectory();
        $nombreArchivo = time() . '.' . $imagen->extension();
        $imagen->move(public_path($directory), $nombreArchivo);

        return $directory . '/' . $nombreArchivo;
    }

    public function procesarImagenParaActualizacion(
        ?UploadedFile $imagen,
        Producto $producto
    ): string {
        if (!$imagen || !$imagen->isValid()) {
            return $producto->imagen ?? $this->getDefaultImage();
        }

        $this->eliminarImagenSiExiste($producto);

        return $this->procesarImagen($imagen);
    }

    public function eliminarImagenSiExiste(Producto $producto): void
    {
        $defaultImage = $this->getDefaultImage();

        if ($producto->imagen &&
            $producto->imagen !== $defaultImage &&
            file_exists(public_path($producto->imagen))) {
            unlink(public_path($producto->imagen));
        }
    }
}

