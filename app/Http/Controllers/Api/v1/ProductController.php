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
    /**
     * Get Method: Get all products
     */
    public function index(Request $request)
    {
        try {
            /**
             * Get the number of records per page. 
             * This is passed in the route as parameter /products?per_page=1, 
             * if the parameter is not sent it takes 10 by default.
             */
            $perPage = $request->query('per_page', 10);
            
            /**
             * Obtain products with information about their variants using 
             * the relationship between models, using paginate for pagination.
             */
            $products = Product::with('variants')->paginate($perPage);

            //If there are no products in the database we return a 404.
            if($products->isEmpty()) {
                return response()->json(["message" => "Products Not Found"], 404);
            }

            return response()->json($products, 200);
        } catch(\Throwable $th) {
            
            \Log::error('Error Getting products: ' . $th->getMessage(), [
                'stack' => $th->getTraceAsString(), // Detailed stacktrace information
            ]);

            return response()->json([
                'error' => 'Error Getting products',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Get Method: Get Product By ID
     */
    public function show(int $id)
    {
        try {
            //Get a product by ID
            $product = Product::with('variants')->findOrFail($id);
            return response()->json($product, 200);
        } catch(ModelNotFoundException $e) {

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

    /**
     * Post Method: Save Product
     */
    public function store(Request $request)
    {
        //Start transaction
        \DB::beginTransaction();

        try {

            //Save product to database.
            $productData = $request->only(['name', 'description', 'price', 'other_attributes']);
            $product = Product::create($productData);
    
           // Save the associated variants
            if ($request->has('variants')) {
                $variants = $request->input('variants');
                foreach ($variants as $variant) {
                    $variant['product_id'] = $product->id; // Associate the product
                    ProductVariant::create($variant);
                }
            }
    
            //If no error has occurred, the transaction for product and the transaction for variants are saved.
            \DB::commit();
    
            return response()->json($product->load('variants'), 201); // Return the product with the variants
        } catch (\Exception $e) {
            //If an error occurs, no information is saved in the database for either the product or the variants.
            \DB::rollBack();

            \Log::error('Error creating product and variants: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);
    
            return response()->json(['error' => 'Failed to create product'], 500);
        }
    }

    /**
     * Post Method: Update Product
     */
    public function update(Request $request, int $id)
    {
        try {
            
            //Find Product in Database.
            $product = Product::with('variants')->findOrFail($id);

            //Update the product found with the data sent in the body of the request.
            $product->update($request->all());
            return response()->json($product, 200);
        } catch (ModelNotFoundException $e) {

            return response()->json([
                'error' => 'Product not found',
                'message' => $e->getMessage()
            ], 404);

        } catch (\Throwable $th) {

            return response()->json([
                'error' => 'Error Getting products',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Post Method: Delete Product
     */
    public function destroy(int $id)
    {
        //Find Product in Database.
        $product = Product::find($id);

        //Delete the product found.
        $product->delete();
        return response()->noContent();
    }

    /**
     * Post Method: Search and Filter Products
     */
    public function search(Request $request)
    {
        $query = Product::with('variants');

        if($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if($request->has('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if($request->has('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }
        
        if($request->has('attributes') && $request->has('value')) {
            $attributes = $request->input('attributes');
            $value = $request->input('value');

            $query->whereJsonContains('other_attributes->' .  $attributes, $value);
        }

        if($request->has('color')) {
            $color = $request->input('color');
            $query->whereHas('variants', function (Builder $q) use ($color) {
                $q->where('color', $color);
            });
        }


        $products = $query->paginate();

        return response()->json( $products, 200);
    }

}
