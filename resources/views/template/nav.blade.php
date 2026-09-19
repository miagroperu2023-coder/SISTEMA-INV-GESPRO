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
                    <a class="nav-link active" aria-current="page" href="{{ route('products.index') }}">Productos</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('categories.index') }}">Categorias</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('sizesets.index') }}">Tallas</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('sales.index') }}">Ventas</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ route('caja.index') }}">Caja</a>
                </li>


                @if (auth()->user()->esDuenoOAdmin())
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('sedes.index') }}">Sedes</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('equipo.index') }}">Equipo</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('reports.index') }}">Reportes</a>
                    </li>
                @endif

                @livewire('sede.sede-selector')

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
