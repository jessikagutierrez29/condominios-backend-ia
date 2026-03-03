<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\EmergencyType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmergencyTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $activeCondominiumId = $this->resolveActiveCondominiumId($request);
        $this->rejectCondominiumIdFromRequest($request);

        $emergencyTypes = EmergencyType::query()
            ->where('condominium_id', $activeCondominiumId)
            ->orderBy('name')
            ->get();

        return response()->json($emergencyTypes);
    }

    public function store(Request $request): JsonResponse
    {
        $activeCondominiumId = $this->resolveActiveCondominiumId($request);
        $this->rejectCondominiumIdFromRequest($request);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('emergency_types', 'name')->where(
                    fn ($query) => $query->where('condominium_id', $activeCondominiumId)
                ),
            ],
            'level' => ['required', 'string', Rule::in(['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])],
        ]);

        $emergencyType = EmergencyType::query()->create([
            'condominium_id' => $activeCondominiumId,
            'name' => $validated['name'],
            'level' => $validated['level'],
            'is_active' => true,
        ]);

        return response()->json($emergencyType, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $activeCondominiumId = $this->resolveActiveCondominiumId($request);
        $this->rejectCondominiumIdFromRequest($request);

        $emergencyType = EmergencyType::query()
            ->where('condominium_id', $activeCondominiumId)
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('emergency_types', 'name')
                    ->where(fn ($query) => $query->where('condominium_id', $activeCondominiumId))
                    ->ignore($emergencyType->id),
            ],
            'level' => ['sometimes', 'required', 'string', Rule::in(['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $emergencyType->update($validated);

        return response()->json($emergencyType->fresh());
    }

    public function toggle(Request $request, int $id): JsonResponse
    {
        $activeCondominiumId = $this->resolveActiveCondominiumId($request);
        $this->rejectCondominiumIdFromRequest($request);

        $emergencyType = EmergencyType::query()
            ->where('condominium_id', $activeCondominiumId)
            ->where('id', $id)
            ->firstOrFail();

        $emergencyType->is_active = ! $emergencyType->is_active;
        $emergencyType->save();

        return response()->json([
            'message' => $emergencyType->is_active
                ? 'Tipo de emergencia activado.'
                : 'Tipo de emergencia desactivado.',
            'data' => $emergencyType,
        ]);
    }

    private function resolveActiveCondominiumId(Request $request): int
    {
        $activeCondominiumId = (int) $request->attributes->get('activeCondominiumId');

        if ($activeCondominiumId <= 0) {
            throw ValidationException::withMessages([
                'condominium' => ['No hay condominio activo resuelto para esta operacion.'],
            ]);
        }

        return $activeCondominiumId;
    }

    private function rejectCondominiumIdFromRequest(Request $request): void
    {
        if ($request->query->has('condominium_id') || $request->request->has('condominium_id')) {
            throw ValidationException::withMessages([
                'condominium_id' => ['No se permite enviar condominium_id en este endpoint.'],
            ]);
        }
    }
}
