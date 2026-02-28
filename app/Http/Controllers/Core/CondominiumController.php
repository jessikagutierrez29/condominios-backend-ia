<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\Condominium;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CondominiumController extends Controller
{
    public function index(): JsonResponse
    {
        $condominiums = Condominium::query()
            ->orderByDesc('id')
            ->get();

        return response()->json($condominiums);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tenant_code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:condominiums,tenant_code'],
            'type' => ['nullable', 'string', 'max:100'],
            'common_areas' => ['nullable', 'string'],
            'tower' => ['nullable', 'string', 'max:100'],
            'floors' => ['nullable', 'integer', 'min:1'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_info' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $condominium = Condominium::query()->create($data);

        return response()->json($condominium, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $condominium = Condominium::query()->findOrFail($id);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'tenant_code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('condominiums', 'tenant_code')->ignore($condominium->id),
            ],
            'type' => ['nullable', 'string', 'max:100'],
            'common_areas' => ['nullable', 'string'],
            'tower' => ['nullable', 'string', 'max:100'],
            'floors' => ['nullable', 'integer', 'min:1'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_info' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $condominium->update($data);

        return response()->json($condominium->fresh());
    }

    public function toggle(int $id): JsonResponse
    {
        $condominium = Condominium::query()->findOrFail($id);

        $condominium->is_active = ! $condominium->is_active;
        $condominium->save();

        return response()->json([
            'message' => $condominium->is_active
                ? 'Condominio activado.'
                : 'Condominio desactivado.',
            'data' => $condominium,
        ]);
    }
}

