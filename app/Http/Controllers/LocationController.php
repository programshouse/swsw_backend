<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Government;
use App\Models\Area;

class LocationController extends Controller
{
    public function index()
    {
        $governments = Government::all();

        return view('admin.governments.index', compact('governments'));
    }


    public function Areas()
    {
        $areas = Area::with('government')
            ->latest()
            ->get();

        $governments = Government::all();

        return view('admin.areas.index', compact(
            'areas',
            'governments'
        ));
    }
}
