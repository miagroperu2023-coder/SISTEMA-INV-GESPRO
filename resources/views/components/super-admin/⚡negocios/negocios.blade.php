<div class="container py-4">
    <h2 class="h4 fw-bold mb-4">Panel de suscripciones — Super Admin</h2>

    <table class="table table-sm align-middle">
        <thead>
            <tr>
                <th>Negocio</th>
                <th>Régimen</th>
                <th>Sedes</th>
                <th>Estado</th>
                <th>Vence</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($negocios as $negocio)
                <tr>
                    <td>{{ $negocio->nombre_comercial }}</td>
                    <td>{{ $negocio->regimen_tributario }}</td>
                    <td>{{ $negocio->locations_count }}</td>
                    <td>
                        <span class="badge {{ $negocio->estado_suscripcion === 'ACTIVO' ? 'bg-success' : 'bg-danger' }}">
                            {{ $negocio->estado_suscripcion }}
                        </span>
                    </td>
                    <td>{{ $negocio->suscripcion_vence_el?->format('d/m/Y') ?? '—' }}</td>
                    <td>
                        <button wire:click="extenderSuscripcion({{ $negocio->id }})"
                            class="btn btn-sm btn-outline-success">+30 días</button>
                        <button wire:click="toggleSuscripcion({{ $negocio->id }})"
                            wire:confirm="¿Confirmas cambiar el estado de este negocio?"
                            class="btn btn-sm btn-outline-{{ $negocio->estado_suscripcion === 'ACTIVO' ? 'danger' : 'success' }}">
                            {{ $negocio->estado_suscripcion === 'ACTIVO' ? 'Suspender' : 'Reactivar' }}
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
