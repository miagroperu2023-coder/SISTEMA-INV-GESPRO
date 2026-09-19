<div class="dropdown">
    @if ($sedes->count() > 1)
        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
            @php $actual = $sedes->firstWhere('id', $sedeActivaId); @endphp
            {{ $actual->nombre ?? 'Selecciona sede' }}
        </button>
        <ul class="dropdown-menu">
            @foreach ($sedes as $sede)
                <li>
                    <button class="dropdown-item {{ $sede->id === $sedeActivaId ? 'active' : '' }}"
                        wire:click="cambiarSede({{ $sede->id }})">
                        {{ $sede->nombre }}
                    </button>
                </li>
            @endforeach
        </ul>
    @else
        {{-- si solo tiene 1 sede (caso típico de vendedora), muestra el nombre fijo sin dropdown --}}
        <span class="badge bg-light text-dark border mt-2">{{ $sedes->first()->nombre ?? 'Sin sede asignada' }}</span>
    @endif
</div>
