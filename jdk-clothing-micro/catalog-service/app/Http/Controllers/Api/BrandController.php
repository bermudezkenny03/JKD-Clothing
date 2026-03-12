<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Support\Str;
use Exception;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        try {
            $brands = Brand::when($request->search, function ($query) use ($request) {
                $query->search($request->search);
            })
                ->ordered()
                ->active()
                ->get();
            return response()->json([
                'success' => true,
                'data' => $brands
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las marcas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:brands,name',
            ]);

            $brand = Brand::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Marca creada correctamente',
                'data' => $brand
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear marca',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function show(Brand $brand)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $brand->load('products')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener marca',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Brand $brand)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
                'is_active' => 'boolean'
            ]);

            $brand->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'is_active' => $request->is_active ?? $brand->is_active,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Marca actualizada correctamente',
                'data' => $brand
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar marca',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Brand $brand)
    {
        try {
            if ($brand->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar una marca con productos asociados'
                ], 400);
            }

            $brand->delete();

            return response()->json([
                'success' => true,
                'message' => 'Marca eliminada correctamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar marca',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
