<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerificarAccesoSede
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && session()->has('sede_activa_id')) {
            $user = Auth::user();

            // rolEnSedeActiva() ya revisa el pivot ACTIVO tanto para dueño/admin como para vendedor
            if (!$user->rolEnSedeActiva()) {
                session()->forget('sede_activa_id');
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Tu acceso a esta tienda fue desactivado. Contacta al dueño del negocio.');
            }
        }

        return $next($request);
    }
}
