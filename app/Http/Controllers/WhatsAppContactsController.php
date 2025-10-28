<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppContacts;
use App\Models\WhatsAppRecord;
use App\Http\Requests\StoreWhatsAppContactsRequest;
use App\Http\Requests\UpdateWhatsAppContactsRequest;
use Illuminate\Http\Request;

class WhatsAppContactsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return WhatsAppContacts::all();
    }

    /**
     * Get the next WhatsApp number.
     */
    public function nextWhatsAppNumber()
    {
        $lastRecord = WhatsAppRecord::latest('id')->first();

        if ($lastRecord && $lastRecord->whats_app_contact_id) {
            $nextContact = WhatsAppContacts::where('id', '>', $lastRecord->whats_app_contact_id)
                ->orderBy('id')
                ->first();
            if (!$nextContact) {
                $nextContact = WhatsAppContacts::orderBy('id')->first();
            }
        } else {
            $nextContact = WhatsAppContacts::orderBy('id')->first();
        }

        if (!$nextContact) {
            return response()->json(['message' => 'No contacts found'], 404);
        }

        return response()->json([
            'next_whatsapp_number' => $nextContact->phone,
            'contact_id' => $nextContact->id,
        ]);
    }

    /** 
     * record a click on phone number
     */

     public function recordWhatsAppNumber(Request $request, WhatsAppContacts $whatsapp_contact){
        $whatsapp_contact->records()->create([
            'whats_app_contact_id' => $whatsapp_contact->id,
        ]);

        return response()->json([
            'message' => 'Phone number clicked',
        ]);
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
        ]);

        return WhatsAppContacts::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(WhatsAppContacts $whatsapp_contact)
    {
        return $whatsapp_contact;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WhatsAppContacts $whatsapp_contact)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WhatsAppContacts $whatsapp_contact)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
        ]);

        $whatsapp_contact->update($validated);

        return $whatsapp_contact;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WhatsAppContacts $whatsapp_contact)
    {
        $whatsapp_contact->delete();

        return response()->noContent();
    }
}
