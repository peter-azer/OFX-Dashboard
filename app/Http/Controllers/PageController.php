<?php

namespace App\Http\Controllers;

use App\Models\About;
use App\Models\Brand;
use App\Models\Hero;
use App\Models\Service;
use App\Models\Team;
use App\Models\Work;
use App\Models\User;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function home()
    {
        return response()->json([
            'hero' => Hero::orderBy('order','asc')->get(),
            'brands' => Brand::orderBy('order','asc')->get(),
            'about' => About::all(),
            'services' => Service::orderBy('order','asc')->get(),
            'works' => Work::orderBy('order','asc')->get(),
            'teams' => Team::all(),
            'users' => User::all(),
        ]);
    }
}
