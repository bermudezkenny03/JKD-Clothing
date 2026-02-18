<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $categories = Category::with('children')
                ->when($request->search, function ($query) use ($request) {
                    $query->search($request->search);
                })
                ->ordered()
                ->get();
            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar categorías',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'parent_id' => 'nullable|exists:categories,id',
            ]);

            $category = Category::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'parent_id' => $request->parent_id,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categoría creada correctamente',
                'data' => $category
            ], 201);

        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al crear categoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        try {

            return response()->json([
                'success' => true,
                'data' => $category->load('children', 'products')
            ]);

        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener categoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'parent_id' => 'nullable|exists:categories,id',
                'is_active' => 'boolean'
            ]);

            $category->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'parent_id' => $request->parent_id,
                'is_active' => $request->is_active ?? $category->is_active,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categoría actualizada correctamente',
                'data' => $category
            ]);

        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar categoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {

            if ($category->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar una categoría con productos asociados'
                ], 400);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Categoría eliminada correctamente'
            ]);

        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar categoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
