<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\IssueType;
use App\Http\Resources\TicketResource;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function issueTypes()
    {
        $issueTypes = IssueType::where('is_active', 1)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $issueTypes,
        ]);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Order $order)
    {
        $request->validate([
            'order_status' => 'required|string',
            'issue_type_id' => 'nullable|exists:issue_types,id',
            'details' => 'required|string|max:1000',
            'image' => 'nullable|image|mimes:png,jpeg,jpg|max:2048',
        ]);

        $delivery = $request->user();

        if (!$delivery || !$delivery->area_id) {
            return response()->json([
                'status' => false,
                'message' => 'Delivery area not found',
                'data' => null
            ], 422);
        }

        if (Ticket::where('order_id', $order->id)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'your ticket was already sent',
                'data' => null
            ], 422);
        }

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('orders-tickets', 'public');
        }

        $ticket = Ticket::create([
            'delivery_user_id' => $delivery->id,
            'area_id' => $delivery->area_id,
            'order_id' => $order->id,
            'order_status' => $request->order_status,
            'issue_type_id' => $request->issue_type_id ?? null,
            'details' => $request->details,
            'image' => $imagePath,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'your ticket sent successfully',
            'data' => new TicketResource($ticket->load('issueType')),
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
