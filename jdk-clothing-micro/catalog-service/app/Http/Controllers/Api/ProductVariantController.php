<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ProductVariantController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'sku' => 'required|string|unique:product_variants,sku',
                'price' => 'required|numeric|min:0',
                'sale_price' => 'nullable|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'attribute_value_ids' => 'required|array',
                'attribute_value_ids.*' => 'exists:attribute_values,id'
            ]);

            $variant = ProductVariant::create([
                'product_id' => $request->product_id,
                'sku' => $request->sku,
                'price' => $request->price,
                'sale_price' => $request->sale_price,
                'stock' => $request->stock,
                'is_active' => true,
            ]);

            // Asociar atributos (color, talla, etc.)
            $variant->attributeValues()->attach($request->attribute_value_ids);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Variante creada correctamente',
                'data' => $variant->load('attributeValues')
            ], 201);
        } catch (Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al crear variante',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function show(ProductVariant $productVariant)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $productVariant->load('attributeValues', 'product')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener variante',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function destroy(ProductVariant $productVariant)
    {
        try {
            $productVariant->delete();

            return response()->json([
                'success' => true,
                'message' => 'Variante eliminada correctamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar variante',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
