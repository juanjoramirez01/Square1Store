<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShoppingCart;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $orders = $request->user()->orders()
                ->with('items.variant.product')
                ->paginate(10);
            
            return response()->json($orders, 200);
        } catch (\Throwable $th) {
            Log::error("Error fetching orders: " . $th->getMessage(), [
                "stack" => $th->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Error fetching orders', 'error' => $th->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "payment_method" => "required|string",
            "shipping_address" => "required|string",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Error validating user data",
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $cart = $user->cart;

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        DB::beginTransaction();

        try {
            $order = $user->orders()->create([
                'total_amount' => $cart->items->sum(fn($item) => $item->quantity * $item->unit_price),
                'order_status' => 'pending',
                'payment_method' => $request->payment_method,
                'shipping_address' => $request->shipping_address,
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                ]);

                $item->variant->decrement('stock_quantity', $item->quantity);
            }

            $cart->items()->delete();
            $cart->delete();

            DB::commit();

            return response()->json($order->load('items.variant.product'), 201);

        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error("Error creating order: " . $th->getMessage(), [
                "stack" => $th->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Error creating order', 'error' => $th->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $order = Order::with('items.variant.product')->find($id);

            if (! Gate::allows('user-view-order', $order)) {
                return response()->json(['message' => 'Sorry, You dont have access to this resources'], 403);
            }

            return response()->json($order, 200);
        } catch (ModelNotFoundException $e) {
            Log::error("Error fetching order: " . $e->getMessage(), [
                "stack" => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Order not found'], 404);
        } catch (\Throwable $th) {
            Log::error("Error fetching order: " . $th->getMessage(), [
                "stack" => $th->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Error fetching order', 'error' => $th->getMessage()], 500);
        }
    }
}
