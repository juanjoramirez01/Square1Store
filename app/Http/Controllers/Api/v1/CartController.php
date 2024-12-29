<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\CartItem;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartController extends Controller
{
    public function index(Request $request)
    {
        try {
            $cart = $request->user()->cart; // Obtiene el carrito del usuario autenticado
            return response()->json($cart->load('items.variant.product'));
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error fetching cart', 'error' => $th->getMessage()], 500);
        }
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $cart = $request->user()->cart;

            // Agregar el producto al carrito
            $cartItem = $cart->items()->create([
                'product_variant_id' => $request->product_variant_id,
                'quantity' => $request->quantity,
                'unit_price' => $request->variant->price,
            ]);

            return response()->json($cartItem, 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error adding item to cart', 'error' => $th->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $cartItem = CartItem::findOrFail($id);
            $cartItem->update(['quantity' => $request->quantity]);

            return response()->json($cartItem, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Cart item not found'], 404);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error updating cart item', 'error' => $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $cartItem = CartItem::findOrFail($id);
            $cartItem->delete();

            return response()->json(['message' => 'Item removed from cart'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Cart item not found'], 404);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error removing item from cart', 'error' => $th->getMessage()], 500);
        }
    }
}