<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ticket;
use App\Http\Resources\TicketResource;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
     public function store(Request $request, Order $order)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'order_status' => 'required|string',
            'type' => 'required|string',
            'details' => 'required|string|max:1000',
            'image' => 'nullable|image|mimes:png,jpeg,jpg|max:2048',
        ]);

        $delivery = $request->user();

        if (Ticket::where('order_id', $order->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'your ticket was already sent',
                'data' => null
            ]);
        }

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('orders-tickets', 'public');
        }

        $ticket = Ticket::create([
            'delivery_user_id' => $delivery->id,
            'area_id' => $request->area_id,
            'order_id' => $order->id,
            'order_status' => $request->order_status,
            'type' => $request->type,
            'details' => $request->details,
            'image' => $imagePath,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'your ticket sent successfully',
            'data' => new TicketResource($ticket)
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
