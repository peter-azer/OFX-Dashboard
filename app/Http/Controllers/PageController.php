<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Hero;
use App\Models\Offer;
use App\Models\Service;
use App\Models\Team;

class PageController extends Controller
{
    public function home()
    {
        return response()->json([
            'hero' => Hero::orderBy('order','asc')->get(),
            'brands' => Brand::orderBy('order','asc')->get(),
            'offers' => Offer::orderBy('order', 'asc')->get(),
            'services' => Service::orderBy('order','asc')->get(),
            'teams' => Team::all()
        ]);
    }
}
