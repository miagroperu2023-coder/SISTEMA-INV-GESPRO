<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GESPRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    @livewireStyles

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo.jpeg') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('logo.jpeg') }}">
    <style>
        body {
            background-color: #f1f4f9;
            min-height: 100vh;
        }

        /* opcional: le da un poco más de "aire" a las tarjetas sobre ese fondo */
        .card {
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        }

        .table {
            background-color: #ffffff;
        }
    </style>
</head>

<body>

    @yield('body')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>

    @livewireScripts
</body>

</html>
