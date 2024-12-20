<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Order;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //Get the logged in user's orders, using model relation.
        $orders = auth()->user()->orders;
        return response()->json($orders, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //Get user orders by id
        $order = Order::with('items')->find($id);

        //Use Gate so that the logged in user can see only their orders. Gate rules are in the boot method of the AppServiceProviders class
        if (! Gate::allows('user-view-order', $order)) {
            return response()->json(['message' => 'Sorry, You dont have access to this resources'], 403);
        }

        return response()->json($order,200);
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
