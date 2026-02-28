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

        // Platform admin: no está atado a un tenant.
        if ($user->is_platform_admin) {
            return $next($request);
        }

        // Tenant user: requiere resolución de condominio desde user_role.
        $role = $user->roles()->first();

        if (! $role || ! isset($role->pivot->condominium_id)) {
            return response()->json([
                'message' => 'El usuario no tiene relación en user_role.',
            ], 404);
        }

        $request->attributes->set('activeCondominiumId', (int) $role->pivot->condominium_id);

        return $next($request);
    }
}
