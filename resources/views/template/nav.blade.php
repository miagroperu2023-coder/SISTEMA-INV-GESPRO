<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <img src="{{ asset('logo.jpeg') }}" alt="" class="img-fluid"
                style="width: 45px; height: 50px; border-radius: 10%">
        </a>
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

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('clients.index') ? 'active' : '' }}"
                        href="{{ route('clients.index') }}">Clientes</a>
                </li>


                @if (auth()->user()->esDuenoOAdmin())
                    {{--
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Gestión Productos
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item {{ request()->routeIs('products.index') ? 'active' : '' }}"
                                    href="{{ route('products.index') }}">Productos</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('categories.index') ? 'active' : '' }}"
                                    href="{{ route('categories.index') }}">Categorias</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('sizesets.index') ? 'active' : '' }}"
                                    href="{{ route('sizesets.index') }}">Tallas</a></li>
                        </ul>
                    </li>
                    --}}

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

                    {{--
                    <li class="nav-item dropdown active">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Gestión Negocio
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item {{ request()->routeIs('configuracion.index') ? 'active' : '' }}"
                                    href="{{ route('configuracion.index') }}">Negocio</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('sedes.index') ? 'active' : '' }}"
                                    href="{{ route('sedes.index') }}">Sedes</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('equipo.index') ? 'active' : '' }}"
                                    href="{{ route('equipo.index') }}">Equipo</a></li>
                        </ul>
                    </li>
                    --}}

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('configuracion.index') ? 'active' : '' }}"
                            href="{{ route('configuracion.index') }}">Negocio</a>
                    </li>

                    @php $negocioActivo = auth()->user()->negocioActivo(); @endphp

                    @if ($negocioActivo && $negocioActivo->tipo_documento === 'ruc')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('series.index') ? 'active' : '' }}"
                                href="{{ route('series.index') }}">Series</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('comprobantes.index') ? 'active' : '' }}"
                                href="{{ route('comprobantes.index') }}">Comprobantes</a>
                        </li>
                    @endif


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

                    @livewire('sede.sede-selector')
                @endif


                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Cerrar Sistema
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="#">Hola: {{ auth()->user()->name }} </a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li class="text-center">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="btn btn-danger w-100">Cerrar Sistema</button>
                            </form>
                        </li>
                    </ul>
                </li>
                {{--
                <li class="nav-item">
                    <a class="nav-link">Hola: {{ auth()->user()->name }} </a>
                </li>

                <li class="nav-item">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-danger">Cerrar Sistema</button>
                    </form>
                </li>
                --}}

            </ul>
        </div>
    </div>
</nav>
