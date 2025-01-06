<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\ShoppingCart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function index(Request $request)
    {
        try {
            $cart = $request->user()->cart()->firstOrCreate(['status' => 'active']);

            $total = $cart->items->sum(fn($item) => $item->quantity * $item->variant->price);

            return response()->json([
                'cart_items' => $cart->items,
                'total' => $total
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->handleException($e, 'Cart not found', 404);
        } catch (\Throwable $th) {
            return $this->handleException($th, 'Error fetching cart', 500);
        }
    }

    public function add(Request $request)
    {
        $validator = $this->validateAddItem($request);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error validating product data',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validatedData = $validator->validated();

        try {
            $variant = ProductVariant::with('product')->findOrFail($validatedData['product_variant_id']);
            if ($validatedData['quantity'] > $variant->stock_quantity) {
                return response()->json([
                    'message' => 'Quantity exceeds available stock',
                ], 422);
            }

            $existingItem = $cart->items()->where('product_variant_id', $validatedData['product_variant_id'])->first();
            if ($existingItem) {
                return response()->json([
                    'message' => 'Variant already in cart!',
                ], 422);
            }

            $cartItem = $cart->items()->create([
                'product_variant_id' => $validatedData['product_variant_id'],
                'quantity' => $validatedData['quantity'],
                'unit_price' => $variant->product->price
            ]);

            return response()->json([
                'message' => 'Item added to cart successfully',
                'cart_item' => $cartItem
            ], 201);
        } catch (\Throwable $th) {
            return $this->handleException($th, 'Error adding item to cart', 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $cartItem = $this->findCartItem($id);

            $variant = ProductVariant::findOrFail($cartItem->product_variant_id);
            if ($validatedData['quantity'] > $variant->stock_quantity) {
                return response()->json([
                    'message' => 'Quantity exceeds available stock',
                ], 422);
            }

            $cartItem->update(['quantity' => $validatedData['quantity']]);

            return response()->json([
                'message' => 'Cart item updated successfully',
                'cart_item' => $cartItem
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->handleException($e, 'Cart item not found', 404);
        } catch (\Throwable $th) {
            return $this->handleException($th, 'Error updating cart item', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $cartItem = $this->findCartItem($id);
            $cartItem->delete();

            return response()->json([
                'message' => 'Item removed from cart'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return $this->handleException($e, 'Cart item not found', 404);
        } catch (\Throwable $th) {
            return $this->handleException($th, 'Error removing item from cart', 500);
        }
    }

    private function findCartItem($id)
    {
        Log::info("Searching for cart item with ID: {$id} for user ID: " . Auth::id());

        $cartItem = CartItem::where('id', $id)
            ->whereHas('cart', fn($query) => $query->where('user_id', Auth::id()))
            ->first();

        if ($cartItem) {
            Log::info("Cart item found: ", ['cart_item' => $cartItem]);
            return $cartItem;
        } else {
            Log::warning("Cart item not found with ID: {$id} for user ID: " . Auth::id());
            throw new ModelNotFoundException("Cart item not found");
        }
    }

    private function validateAddItem(Request $request)
    {
        return Validator::make($request->all(), [
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);
    }

    private function handleException(\Throwable $e, string $message, int $statusCode)
    {
        Log::error("{$message}: " . $e->getMessage(), [
            "stack" => $e->getTraceAsString(),
        ]);
        return response()->json([
            'message' => $message,
            'error' => $e->getMessage()
        ], $statusCode);
    }
}
