<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Menu</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('sales.index') ? 'active' : '' }}"
                        href="{{ route('sales.index') }}">Ventas</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('caja.index') ? 'active' : '' }}"
                        href="{{ route('caja.index') }}">Caja</a>
                </li>




                @if (auth()->user()->esDuenoOAdmin())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('products.index') ? 'active' : '' }}"
                            href="{{ route('products.index') }}">Productos</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('categories.index') ? 'active' : '' }}"
                            href="{{ route('categories.index') }}">Categorias</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('sizesets.index') ? 'active' : '' }}"
                            href="{{ route('sizesets.index') }}">Tallas</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('sedes.index') ? 'active' : '' }}"
                            href="{{ route('sedes.index') }}">Sedes</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('equipo.index') ? 'active' : '' }}"
                            href="{{ route('equipo.index') }}">Equipo</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}"
                            href="{{ route('reports.index') }}">Reportes</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('configuracion.index') ? 'active' : '' }}"
                            href="{{ route('configuracion.index') }}">Negocio</a>
                    </li>

                    @livewire('sede.sede-selector')
                @endif

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('clients.index') ? 'active' : '' }}"
                        href="{{ route('clients.index') }}">Clientes</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link">Hola: {{ auth()->user()->name }} </a>
                </li>

                <li class="nav-item">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-danger">Cerrar Sistema</button>
                    </form>
                </li>

            </ul>
        </div>
    </div>
</nav>
