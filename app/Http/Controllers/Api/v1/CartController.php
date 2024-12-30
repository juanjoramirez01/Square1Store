<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\ShoppingCart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index(Request $request)
    {
        try {
            $cart = $request->user()->cart()->with('items.variant.product')->firstOrFail();

            $total = $cart->items->sum(function($item) {
                return $item->quantity * $item->variant->price;
            });

            return response()->json([
                'cart_items' => $cart->items,
                'total' => $total
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cart not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error fetching cart',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function add(Request $request)
    {
        $validatedData = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $cart = $request->user()->cart()->firstOrCreate([]);
            $variant = ProductVariant::findOrFail($validatedData['product_variant_id']);

            $cartItem = $cart->items()->firstOrNew([
                'product_variant_id' => $validatedData['product_variant_id']
            ]);

            $cartItem->quantity += $validatedData['quantity'];
            $cartItem->unit_price = $variant->price;
            $cartItem->save();

            return response()->json([
                'message' => 'Item added to cart successfully',
                'cart_item' => $cartItem
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error adding item to cart',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $cartItem = $this->findCartItem($id);

            $cartItem->update(['quantity' => $validatedData['quantity']]);

            return response()->json([
                'message' => 'Cart item updated successfully',
                'cart_item' => $cartItem
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cart item not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error updating cart item',
                'error' => $th->getMessage()
            ], 500);
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
            return response()->json([
                'message' => 'Cart item not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error removing item from cart',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    private function findCartItem($id)
    {
        return CartItem::where('id', $id)
            ->whereHas('shoppingCart', function($query) {
                $query->where('user_id', Auth::id());
            })
            ->firstOrFail();
    }
}