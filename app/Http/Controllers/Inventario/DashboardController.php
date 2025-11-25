<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:VER DASHBOARD INVENTARIO')->only(['index']);
    }

    /**
     * Muestra el dashboard de inventario usando Livewire
     *
     * @return View
     */
    public function index(): View
    {
        return view('inventario.dashboard.index');
    }
}