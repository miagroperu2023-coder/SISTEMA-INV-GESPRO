<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function index()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'nombre_comercial' => 'required|string|max:255',
            'nombre_sede' => 'required|string|max:255',
            'tipo_documento' => 'required|in:ruc,sin_ruc',
            'numero_documento' => 'required_if:tipo_documento,ruc|nullable|string|max:20',
            'razon_social' => 'required_if:tipo_documento,ruc|nullable|string|max:255',
            'regimen_tributario' => 'required_if:tipo_documento,ruc|nullable|in:nrus,general',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
            ]);

            $business = Business::crearConSedeInicial([
                'nombre_comercial' => $validated['nombre_comercial'],
                'tipo_documento' => $validated['tipo_documento'],
                'numero_documento' => $validated['numero_documento'] ?? null,
                'razon_social' => $validated['razon_social'] ?? null,
                'regimen_tributario' => $validated['tipo_documento'] === 'ruc'
                    ? $validated['regimen_tributario']
                    : 'sin_ruc',
            ], $validated['nombre_sede']);

            $business->users()->attach($user->id, ['rol' => 'dueño']);

            Auth::login($user);

            session(['sede_activa_id' => $business->locations()->first()->id]);
        });

        return redirect()->route('products.index')->with('ok', '¡Tu negocio fue creado con éxito!');
    }
}
