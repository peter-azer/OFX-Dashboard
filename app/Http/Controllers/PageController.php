<?php

namespace App\Http\Controllers;

use App\Models\About;
use App\Models\Brand;
use App\Models\Hero;
use App\Models\Service;
use App\Models\Team;
use App\Models\Work;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home()
    {
        return response()->json([
            'hero' => Hero::first(),
            'brands' => Brand::all(),
            'about' => About::first(),
            'services' => Service::all(),
            'works' => Work::all(),
            'teams' => Team::all(),
        ]);
    }
}
