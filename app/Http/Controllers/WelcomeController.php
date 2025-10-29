<?php

namespace App\Http\Controllers;

use App\Models\Hero;
use App\Models\Brand;
use App\Models\About;
use App\Models\Service;
use App\Models\Work;
use App\Models\Team;
use App\Models\PhoneContacts;
use App\Models\WhatsAppContacts;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
            $hero = Hero::all();
            $brands = Brand::all();
            $about = About::all();
            $services = Service::all();
            $works = Work::all();
            $team = Team::all();
            $phoneContacts = PhoneContacts::all();
            $whatsappContacts = WhatsAppContacts::all();

        return view('welcome', ['hero'=>$hero, 'brands'=>$brands, 'about'=>$about, 'services'=>$services, 'works'=>$works, 'team'=>$team, 'phoneContacts'=>$phoneContacts, 'whatsappContacts'=>$whatsappContacts]);
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // Here you would typically save the contact form submission to the database
        // For example:
        // ContactSubmission::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your message! We will get back to you soon.'
        ]);
    }
}
