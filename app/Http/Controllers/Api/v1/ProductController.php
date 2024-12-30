<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $products = Product::with('variants')->paginate($perPage);

            if ($products->isEmpty()) {
                return response()->json(["message" => "Products Not Found"], 404);
            }

            $products->transform(function ($product) {
                $product->other_attributes = json_decode($product->other_attributes, true);
                return $product;
            });

            return response()->json($products, 200);
        } catch (\Throwable $th) {
            \Log::error('Error Getting products: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Error Getting products',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function show(int $id)
    {
        try {
            $product = Product::with('variants')->findOrFail($id);
            $product->other_attributes = json_decode($product->other_attributes, true);
            return response()->json($product, 200);
        } catch (ModelNotFoundException $e) {
            \Log::error('Error fetching product: ' . $e->getMessage(), [
                'product_id' => $id,
                'stack' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Product not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        \DB::beginTransaction();

        try {
            $productData = $request->only(['name', 'description', 'price', 'other_attributes']);
            $product = Product::create($productData);

            if ($request->has('variants')) {
                $variants = $request->input('variants');
                foreach ($variants as $variant) {
                    $variant['product_id'] = $product->id;
                    ProductVariant::create($variant);
                }
            }

            \DB::commit();

            $product->other_attributes = json_decode($product->other_attributes, true);
            return response()->json($product->load('variants'), 201);
        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error('Error creating product and variants: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);

            return response()->json(['error' => 'Failed to create product'], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $product = Product::with('variants')->findOrFail($id);
            $product->update($request->all());
            $product->other_attributes = json_decode($product->other_attributes, true);
            return response()->json($product, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Product not found',
                'message' => $e->getMessage()
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Error updating product',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            return response()->noContent();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Product not found',
                'message' => $e->getMessage()
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Error deleting product',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $query = Product::with('variants');
    
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }
    
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
    
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }
    
        if ($request->has('attributes') && $request->has('value')) {
            $attributes = $request->input('attributes');
            $value = $request->input('value');
            $query->where('other_attributes', 'like', '%\"' . $attributes . '\":\"%' . $value . '%\"%');
        }
    
        if ($request->color || $request->size) {
            $query->whereHas('variants', function($q) use ($request) {
                if ($request->color) {
                    $q->where('color', $request->color);
                }
                if ($request->size) {
                    $q->where('size', $request->size);
                }
            });
        }
    
        $products = $query->paginate();
        $products->transform(function ($product) {
            $product->other_attributes = json_decode($product->other_attributes, true);
            return $product;
        });

        return response()->json($products, 200);
    }
    
}