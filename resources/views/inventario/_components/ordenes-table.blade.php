<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @php
                $emptyStates = [
                    'EN ESPERA' => [
                        'icon' => 'fas fa-hourglass-half',
                        'iconColor' => 'text-warning',
                        'title' => 'No hay órdenes pendientes',
                        'description' => 'Todas las solicitudes han sido procesadas o no hay nuevas órdenes por aprobar.'
                    ],
                    'APROBADA' => [
                        'icon' => 'fas fa-check-circle',
                        'iconColor' => 'text-success',
                        'title' => 'No hay órdenes aprobadas',
                        'description' => 'Todavía no se han cerrado solicitudes exitosamente.'
                    ],
                    'RECHAZADA' => [
                        'icon' => 'fas fa-times-circle',
                        'iconColor' => 'text-danger',
                        'title' => 'No hay órdenes rechazadas',
                        'description' => 'No se han registrado cancelaciones o rechazos.'
                    ],
                    'DEFAULT' => [
                        'icon' => 'fas fa-list',
                        'iconColor' => 'text-secondary',
                        'title' => 'No hay órdenes para mostrar',
                        'description' => 'Aún no existen órdenes que cumplan los filtros seleccionados.'
                    ]
                ];

                $stateKey = strtoupper($estado ?? 'DEFAULT');
                $emptyState = $emptyStates[$stateKey] ?? $emptyStates['DEFAULT'];
            @endphp

            <div class="card">
                <div class="card-header with-border">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Listado de Órdenes
                    </h3>
                </div>
                <div class="card-body">
                    @if($ordenes && count($ordenes) > 0)
                        <div class="table-responsive">
                            <table
                                class="table table-hover table-striped"
                                aria-describedby="ordenes-description"
                            >
                                <caption id="ordenes-description" class="sr-only">
                                    Listado de órdenes con información de usuario, tipo, estado,
                                    fecha, cantidad de ítems y acciones disponibles.
                                </caption>
                                <thead>
                                    <tr>
                                        <th style="width: 5%">#</th>
                                        <th style="width: 10%">ID Orden</th>
                                        <th style="width: 15%">Usuario</th>
                                        <th style="width: 10%">Tipo</th>
                                        <th style="width: 15%">Estado</th>
                                        <th style="width: 15%">Fecha</th>
                                        <th style="width: 15%">Cantidad Items</th>
                                        <th style="width: 15%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ordenes as $orden)
                                        @php
                                            $tipoNombre = $orden->tipoOrden->parametro->name ?? 'N/A';
                                            $tipoClass = $tipoNombre === 'PRÉSTAMO' ? 'info' : 'warning';

                                            $estadoDetalle = $orden->detalles->first();
                                            $estadoNombre = $estadoDetalle->estadoOrden->parametro->name ?? 'N/A';
                                            $estadoClass = match ($estadoNombre) {
                                                'EN ESPERA' => 'warning',
                                                'APROBADA' => 'success',
                                                'RECHAZADA' => 'danger',
                                                default => 'secondary'
                                            };

                                            $itemsCount = $orden->detalles ? $orden->detalles->count() : 0;
                                            $fechaCreacion = optional($orden->created_at)->format('d/m/Y H:i');
                                            $estadoActual = $estadoNombre ?? '';
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <span class="badge badge-secondary">{{ $orden->id }}</span>
                                            </td>
                                            <td>{{ $orden->userCreate->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $tipoClass }}">
                                                    {{ $tipoNombre }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $estadoClass }}">
                                                    {{ $estadoNombre }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $fechaCreacion ?? 'N/A' }}
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">
                                                    {{ $itemsCount }} items
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('inventario.ordenes.show', ['orden' => $orden->id, 'ref' => url()->current()]) }}"
                                                   class="btn btn-sm btn-info"
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($estadoActual === 'EN ESPERA')
                                                    <a href="{{ route('inventario.ordenes.index', ['action' => 'edit', 'id' => $orden->id]) }}"
                                                       class="btn btn-sm btn-warning"
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Paginación --}}
                        @if(method_exists($ordenes, 'links'))
                            <div class="d-flex justify-content-center mt-3">
                                {{ $ordenes->links() }}
                            </div>
                        @endif
                    @else
                        @include('inventario._components.empty-state', $emptyState)
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

