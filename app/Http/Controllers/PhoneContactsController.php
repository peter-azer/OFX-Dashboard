<?php

namespace App\Http\Controllers;

use App\Models\PhoneContacts;
use App\Http\Requests\StorePhoneContactsRequest;
use App\Http\Requests\UpdatePhoneContactsRequest;
use App\Models\PhoneRecord;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class PhoneContactsController extends BaseController
{
    /**
     * Create a new controller instance.
    */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show','records', 'nextPhoneNumber', 'recordPhoneNumber']);
        $this->middleware('permission:view phone')->only(['index','records', 'show']);
        $this->middleware('permission:create phone')->only('store');
        $this->middleware('permission:edit phone')->only('update');
        $this->middleware('permission:delete phone')->only('destroy');
    }
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

        if ($lastRecord) {
            // Get the last contact that was called with its counter
            $lastContact = PhoneContacts::find($lastRecord->phone_contacts_id);
            
            if ($lastContact) {
                // Get the max consecutive calls from the contact's counter
                $maxCallsForContact = (int)($lastContact->counter ?? 1);
                
                // Get the number of consecutive calls for the last contact
                $consecutiveCalls = PhoneRecord::where('phone_contacts_id', $lastContact->id)
                    ->orderBy('id', 'desc')
                    ->take($maxCallsForContact)
                    ->count();

                // If we haven't reached the max calls for this contact, return the same contact
                if ($consecutiveCalls < $maxCallsForContact) {
                    return $this->formatResponse($lastContact);
                }
            }
            
            // If we get here, we need to move to the next contact
            $nextContact = PhoneContacts::where('id', '>', $lastRecord->phone_contacts_id)
                ->whereNotNull('counter')
                ->where('counter', '>', 0)
                ->orderBy('id')
                ->first();
                
            // If no next contact, wrap around to the first valid contact
            if (!$nextContact) {
                $nextContact = PhoneContacts::whereNotNull('counter')
                    ->where('counter', '>', 0)
                    ->orderBy('id')
                    ->first();
            }
        } else {
            // No records yet, get the first valid contact
            $nextContact = PhoneContacts::whereNotNull('counter')
                ->where('counter', '>', 0)
                ->orderBy('id')
                ->first();
        }

        if (!$nextContact) {
            return response()->json(['message' => 'No contacts found'], 404);
        }

        return $this->formatResponse($nextContact);
    }
    
    /**
     * Format the response for next phone number
     */
    private function formatResponse(PhoneContacts $contact)
    {
        return response()->json([
            'next_phone_number' => $contact->phone,
            'contact_id' => $contact->id,
            'counter' => $contact->counter ?? 0,
        ]);
    }

    /** 
     * record a click on phone number
     */

     public function recordPhoneNumber(Request $request, PhoneContacts $phone_contact){
        $phone_contact->records()->create([
            'phone_contacts_id' => $phone_contact->id,
        ]);

        return response()->json([
            'message' => 'Phone number clicked',
        ]);
     }

     /**
      * get all phone records
      */
      public function records()
      {
          return PhoneRecord::all();
      }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'counter' => 'nullable|numeric',
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
            'counter' => 'nullable|numeric',
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
