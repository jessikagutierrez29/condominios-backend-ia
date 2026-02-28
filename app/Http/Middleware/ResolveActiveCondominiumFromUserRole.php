<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveActiveCondominiumFromUserRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'No autenticado.',
            ], 401);
        }

        $role = $user->roles()->first();

        // Usuario global: no requiere condominio activo.
        if ($role && $role->name === 'super_admin') {
            return $next($request);
        }

        if (! $role || ! isset($role->pivot->condominium_id)) {
            return response()->json([
                'message' => 'El usuario no tiene relación en user_role.',
            ], 404);
        }

        $request->attributes->set('activeCondominiumId', (int) $role->pivot->condominium_id);

        return $next($request);
    }
}

