<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $areas = Area::select('id', 'name_ar')->get();

        $tickets = Ticket::when($request->area_id, function ($q) {
            $q->where('area_id', request('area_id'));
        })->get();

        return view('admin.tickets.index', compact('tickets', 'areas'));
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();

        return redirect()->route('admin.tickets.index')->with('success', 'تم حذف المشكلة بنجاح');
    }
}
