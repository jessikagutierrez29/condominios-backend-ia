<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\UnitType;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UnitTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $activeCondominiumId = $this->resolveActiveCondominiumId($request);
        $this->rejectCondominiumIdFromRequest($request);

        $unitTypes = UnitType::query()
            ->withCount('apartments')
            ->where('condominium_id', $activeCondominiumId)
            ->orderBy('name')
            ->get();

        return response()->json($unitTypes);
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
                Rule::unique('unit_types', 'name')->where(
                    fn ($q) => $q->where('condominium_id', $activeCondominiumId)
                ),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        try {
            $unitType = UnitType::query()->create([
                'condominium_id' => $activeCondominiumId,
                'name' => $validated['name'],
                'is_active' => $validated['is_active'] ?? true,
            ]);
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                return response()->json([
                    'message' => 'Ya existe ese tipo de unidad en el condominio activo.',
                ], 409);
            }

            throw $exception;
        }

        return response()->json($unitType, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $activeCondominiumId = $this->resolveActiveCondominiumId($request);
        $this->rejectCondominiumIdFromRequest($request);

        $unitType = UnitType::query()
            ->where('condominium_id', $activeCondominiumId)
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('unit_types', 'name')
                    ->where(fn ($q) => $q->where('condominium_id', $activeCondominiumId))
                    ->ignore($unitType->id),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        try {
            $unitType->update($validated);
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                return response()->json([
                    'message' => 'Ya existe ese tipo de unidad en el condominio activo.',
                ], 409);
            }

            throw $exception;
        }

        return response()->json($unitType->fresh());
    }

    public function toggle(Request $request, int $id): JsonResponse
    {
        $activeCondominiumId = $this->resolveActiveCondominiumId($request);
        $this->rejectCondominiumIdFromRequest($request);

        $unitType = UnitType::query()
            ->where('condominium_id', $activeCondominiumId)
            ->where('id', $id)
            ->firstOrFail();

        $unitType->is_active = ! $unitType->is_active;
        $unitType->save();

        return response()->json([
            'message' => $unitType->is_active ? 'Tipo de unidad activado.' : 'Tipo de unidad desactivado.',
            'data' => $unitType,
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
