<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Exception;

class AttributeController extends Controller
{
    public function index()
    {
        try {
            $attributes = Attribute::with('values')->get();

            return response()->json([
                'success' => true,
                'data' => $attributes
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar atributos',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:attributes,name',
            ]);

            $attribute = Attribute::create([
                'name' => $request->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Atributo creado correctamente',
                'data' => $attribute
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear atributo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function show(Attribute $attribute)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $attribute->load('values')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener atributo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function update(Request $request, Attribute $attribute)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:attributes,name,' . $attribute->id,
            ]);

            $attribute->update([
                'name' => $request->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Atributo actualizado correctamente',
                'data' => $attribute
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar atributo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy(Attribute $attribute)
    {
        try {
            $attribute->delete();

            return response()->json([
                'success' => true,
                'message' => 'Atributo eliminado correctamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar atributo',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
