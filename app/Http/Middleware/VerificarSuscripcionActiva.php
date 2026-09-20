<?php

namespace App\Http\Middleware;

use App\Models\BusinessLocation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarSuscripcionActiva
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sede = BusinessLocation::find(session('sede_activa_id'));
        $business = $sede?->business;

        if ($business && $business->estado_suscripcion === 'SUSPENDIDO') {
            auth()->logout();
            return redirect()->route('login')->with('error', 'El acceso de tu negocio está suspendido. Contacta al soporte para regularizar tu pago.');
        }

        return $next($request);
    }
}
