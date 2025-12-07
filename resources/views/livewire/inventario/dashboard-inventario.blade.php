<div wire:poll.5m="cargarDatos">
        {{-- Botón de refrescar --}}
        <div class="row mb-3">
            <div class="col-12 text-right">
                <button
                    type="button"
                    class="btn btn-sm btn-primary"
                    wire:click="refrescar"
                >
                    <i class="fas fa-sync-alt"></i> Refrescar
                </button>
                <span class="ml-2 text-muted small">
                    <i class="fas fa-info-circle"></i> Los datos se actualizan automáticamente
                </span>
            </div>
        </div>

        {{-- Tarjetas de estadísticas --}}
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $totalProductos }}</h3>
                        <p>Total Productos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <a href="{{ route('inventario.productos.index') }}" class="small-box-footer">
                        Ver productos <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $productosPorVencer }}</h3>
                        <p>Productos por Vencer</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <a href="{{ route('inventario.productos.index') }}" class="small-box-footer">
                        Más información <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $productosStockBajo }}</h3>
                        <p>Stock Bajo</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <a href="{{ route('inventario.productos.index') }}" class="small-box-footer">
                        Más información <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $totalCategorias }}</h3>
                        <p>Categorías</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <a href="{{ route('inventario.categorias.index') }}" class="small-box-footer">
                        Ver categorías <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Gráficos --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Productos por Tipo
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="productosConsumibles" wire:ignore></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-2"></i>
                            Productos Más Solicitados
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <caption id="dashboard-description" class="sr-only">
                                    Estadisticas de productos más solicitados con información de producto y cantidad de solicitudes.
                                </caption>
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Solicitudes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($productosMasSolicitados as $producto)
                                    <tr>
                                        <td>{{ $producto['name'] }}</td>
                                        <td>
                                            @php
                                                $maxSolicitudes = collect($productosMasSolicitados)->max('solicitudes') ?: 1;
                                            @endphp
                                            <div class="progress progress-xs">
                                                <div class="progress-bar bg-success" style="width: {{ ($producto['solicitudes'] / $maxSolicitudes) * 100 }}%"></div>
                                            </div>
                                            <span class="badge bg-success">{{ $producto['solicitudes'] }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No hay datos de solicitudes</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Productos Recientes y Categorías --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title">
                            <i class="fas fa-box mr-2"></i>
                            Productos Recientes
                        </h3>
                    </div>
                    <div class="card-body table-responsive p-0" style="height: 300px;">
                        <table class="table table-hover">
                            <caption id="productos-recientes-description" class="sr-only">
                                Lista de productos recientes con información de producto, cantidad, estado y fecha de creación.
                            </caption>
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productosRecientes as $producto)
                                <tr>
                                    <td>{{ $producto['name'] }}</td>
                                    <td>{{ $producto['cantidad'] }}</td>
                                    <td>
                                        @if(isset($producto['estado']) && isset($producto['estado']['parametro']))
                                            <span class="badge {{ $producto['estado']['parametro']['name'] === 'DISPONIBLE' ? 'bg-success' : 'bg-warning' }}">
                                                {{ $producto['estado']['parametro']['name'] }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Sin estado</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($producto['created_at'])->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No hay productos recientes</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-2"></i>
                            Productos por Categoría
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="productosPorCategoria" wire:ignore></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

</div>

@push('css')
    <style>
        .chart-container {
            position: relative;
            height: 300px;
        }
        .progress-xs {
            height: 10px;
        }
    </style>
@endpush

@push('js')
<script>
(function() {
    let chartConsumibles = null;
    let chartCategoria = null;

    function inicializarGraficos() {
        // Gráfico de Productos Consumibles vs No Consumibles
        const ctxConsumibles = document.getElementById('productosConsumibles');
        if (ctxConsumibles) {
            const consumibles = @this.productosConsumibles || 0;
            const noConsumibles = @this.productosNoConsumibles || 0;

            if (!chartConsumibles) {
                chartConsumibles = new Chart(ctxConsumibles.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['Consumibles', 'No Consumibles'],
                        datasets: [{
                            label: 'Cantidad de Productos',
                            data: [consumibles, noConsumibles],
                            backgroundColor: ['#00a65a', '#f39c12'],
                            borderColor: ['#00a65a', '#f39c12'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            } else {
                // Actualizar datos del gráfico existente
                chartConsumibles.data.datasets[0].data = [consumibles, noConsumibles];
                chartConsumibles.update('none');
            }
        }

        // Gráfico de Productos por Categoría
        const ctxCategoria = document.getElementById('productosPorCategoria');
        if (ctxCategoria) {
            const data = @this.productosPorCategoria || [];
            
            if (data && data.length > 0) {
                const labels = data.map(item => item.categoria);
                const values = data.map(item => item.total);

                if (!chartCategoria) {
                    chartCategoria = new Chart(ctxCategoria.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: values,
                                backgroundColor: [
                                    '#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc',
                                    '#d2d6de', '#6c757d', '#007bff', '#17a2b8', '#28a745'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right'
                                }
                            }
                        }
                    });
                } else {
                    // Actualizar datos del gráfico existente
                    chartCategoria.data.labels = labels;
                    chartCategoria.data.datasets[0].data = values;
                    chartCategoria.update('none');
                }
            }
        }
    }

    // Inicializar gráficos cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializarGraficos);
    } else {
        inicializarGraficos();
    }

    // Escuchar eventos de Livewire para actualizar gráficos
    Livewire.on('datos-actualizados', () => {
        setTimeout(inicializarGraficos, 100);
    });

    // Actualizar gráficos cuando Livewire actualice el componente
    Livewire.hook('morph.updated', ({ el, component }) => {
        if (component && component.__instance && component.__instance.__livewire) {
            setTimeout(inicializarGraficos, 100);
        }
    });
})();
</script>
@endpush

