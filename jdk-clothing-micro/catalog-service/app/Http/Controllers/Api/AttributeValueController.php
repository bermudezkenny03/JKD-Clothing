<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Exception;

class AttributeValueController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'attribute_id' => 'required|exists:attributes,id',
                'value' => 'required|string|max:255'
            ]);

            $value = AttributeValue::create([
                'attribute_id' => $request->attribute_id,
                'value' => $request->value
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Valor creado correctamente',
                'data' => $value
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear valor',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy(AttributeValue $attributeValue)
    {
        try {
            $attributeValue->delete();

            return response()->json([
                'success' => true,
                'message' => 'Valor eliminado correctamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar valor',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
