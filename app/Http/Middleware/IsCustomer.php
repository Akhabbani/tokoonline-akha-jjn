<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && in_array((string) auth()->user()->role, ['customer', '2'], true)) {
            return $next($request);
        }

        if (!auth()->check()) {
            return redirect()->route('auth.redirect')->with('msgError', 'Silakan login terlebih dahulu.');
        }

        return redirect()->route('beranda')->with('msgError', 'Halaman ini hanya dapat diakses customer.');
    }
}
