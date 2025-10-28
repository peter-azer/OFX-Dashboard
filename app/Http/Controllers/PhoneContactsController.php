<?php

namespace App\Http\Controllers;

use App\Models\PhoneContacts;
use App\Http\Requests\StorePhoneContactsRequest;
use App\Http\Requests\UpdatePhoneContactsRequest;
use App\Models\PhoneRecord;
use Illuminate\Http\Request;

class PhoneContactsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PhoneContacts::all();
    }

    /**
     * Get the next phone number.
     */
    public function nextPhoneNumber()
    {
        $lastRecord = PhoneRecord::latest('id')->first();

        if ($lastRecord && $lastRecord->phone_contact_id) {
            $nextContact = PhoneContacts::where('id', '>', $lastRecord->phone_contact_id)
                ->orderBy('id')
                ->first();
            if (!$nextContact) {
                $nextContact = PhoneContacts::orderBy('id')->first();
            }
        } else {
            $nextContact = PhoneContacts::orderBy('id')->first();
        }

        if (!$nextContact) {
            return response()->json(['message' => 'No contacts found'], 404);
        }

        return response()->json([
            'next_phone_number' => $nextContact->phone,
            'contact_id' => $nextContact->id,
        ]);
    }

    /** 
     * record a click on phone number
     */

     public function recordPhoneNumber(Request $request, PhoneContacts $phone_contact){
        $phone_contact->records()->create([
            'phone_contact_id' => $phone_contact->id,
        ]);

        return response()->json([
            'message' => 'Phone number clicked',
        ]);
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

        return PhoneContacts::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(PhoneContacts $phone_contact)
    {
        return $phone_contact;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PhoneContacts $phone_contact)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PhoneContacts $phone_contact)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
        ]);

        $phone_contact->update($validated);

        return $phone_contact;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PhoneContacts $phone_contact)
    {
        $phone_contact->delete();

        return response()->noContent();
    }
}
