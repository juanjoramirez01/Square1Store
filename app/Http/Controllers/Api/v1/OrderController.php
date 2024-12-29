<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Order;
use App\Models\OrderItem;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            //Get the logged in user's orders, using model relation.
            $orders = auth()->user()->orders;
            return response()->json($orders, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error fetching orders', 'error' => $th->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $cart = $user->cart;

        if ($cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

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
            }

            $cart->items()->delete();

            return response()->json($order->load('items.variant.product'), 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error creating order', 'error' => $th->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            //Get user orders by id
            $order = Order::with('items.variant.product')->find($id);

            //Use Gate so that the logged in user can see only their orders. Gate rules are in the boot method of the AppServiceProviders class
            if (! Gate::allows('user-view-order', $order)) {
                return response()->json(['message' => 'Sorry, You dont have access to this resources'], 403);
            }

            return response()->json($order, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Order not found'], 404);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error fetching order', 'error' => $th->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}