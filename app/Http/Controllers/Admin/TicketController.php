<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Area;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $areas = Area::select('id', 'name_ar')->get();

        $tickets = Ticket::with([
            'deliveryUser',
            'issueType',
            'order.user',
            'order.kitchen',
            'order.userAddress',
            'area.government',
        ])
            ->when($request->area_id, function ($q) use ($request) {
                $q->where('area_id', $request->area_id);
            })
            ->latest()
            ->get();

        return view('admin.tickets.index', compact('tickets', 'areas'));
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        return redirect()->route('admin.tickets.index')->with('success', 'تم حذف المشكلة بنجاح');
    }
}
