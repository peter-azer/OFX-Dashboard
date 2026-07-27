<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppContacts;
use App\Models\WhatsAppRecord;
use App\Http\Requests\StoreWhatsAppContactsRequest;
use App\Http\Requests\UpdateWhatsAppContactsRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class WhatsAppContactsController extends BaseController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show', 'nextWhatsAppNumber', 'nextJuniorWhatsAppNumber', 'nextSeniorWhatsAppNumber', 'recordWhatsAppNumber']);
        $this->middleware('permission:view whatsapp')->only(['index', 'show']);
        $this->middleware('permission:create whatsapp')->only('store');
        $this->middleware('permission:edit whatsapp')->only('update');
        $this->middleware('permission:delete whatsapp')->only('destroy');
    }
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

        if ($lastRecord) {
            // Get the last contact that was called with its counter
            $lastContact = WhatsAppContacts::find($lastRecord->whats_app_contacts_id);

            if ($lastContact) {
                // Get the max consecutive calls from the contact's counter
                $maxCallsForContact = (int)($lastContact->counter ?? 1);

                // Get the number of consecutive calls for the last contact
                $consecutiveCalls = WhatsAppRecord::where('whats_app_contacts_id', $lastContact->id)
                    ->orderBy('id', 'desc')
                    ->take($maxCallsForContact)
                    ->count();

                // If we haven't reached the max calls for this contact, return the same contact
                if ($consecutiveCalls < $maxCallsForContact) {
                    return $this->formatWhatsAppResponse($lastContact);
                }
            }

            // If we get here, we need to move to the next contact
            $nextContact = WhatsAppContacts::where('id', '>', $lastRecord->whats_app_contacts_id)
                ->whereNotNull('counter')
                ->where('counter', '>', 0)
                ->orderBy('id')
                ->first();

            // If no next contact, wrap around to the first valid contact
            if (!$nextContact) {
                $nextContact = WhatsAppContacts::whereNotNull('counter')
                    ->where('counter', '>', 0)
                    ->orderBy('id')
                    ->first();
            }
        } else {
            // No records yet, get the first valid contact
            $nextContact = WhatsAppContacts::whereNotNull('counter')
                ->where('counter', '>', 0)
                ->orderBy('id')
                ->first();
        }

        if (!$nextContact) {
            return response()->json(['message' => 'No contacts found'], 404);
        }

        return $this->formatWhatsAppResponse($nextContact);
    }

    /**
     * Get the next junior WhatsApp number.
     */
    public function nextJuniorWhatsAppNumber()
    {
        return $this->getNextWhatsAppNumberByType("junior");
    }

    /**
     * Get the next senior WhatsApp number.
     */
    public function nextSeniorWhatsAppNumber()
    {
        return $this->getNextWhatsAppNumberByType("senior");
    }

    /**
     * Get the next WhatsApp number by type (junior/senior).
     */
    private function getNextWhatsAppNumberByType($type)
    {
        try {
            return DB::transaction(function () use ($type) {
                // Lock the last record of this type so concurrent requests can't read a stale state
                $lastRecord = WhatsAppRecord::with('whatsAppContact')
                    ->whereHas('whatsAppContact', function ($query) use ($type) {
                        $query->where('type', $type);
                    })
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();

                $lastContact = $lastRecord?->whatsAppContact;

                if ($lastContact && $lastContact->type == $type) {
                    $maxCallsForContact = max(1, (int) ($lastContact->counter ?? 1));

                    // Get the IDs of the most recent records for this type
                    $recentRecords = WhatsAppRecord::whereHas('whatsAppContact', function ($query) use ($type) {
                        $query->where('type', $type);
                    })
                        ->orderBy('id', 'desc')
                        ->take($maxCallsForContact)
                        ->pluck('whats_app_contacts_id')
                        ->toArray();

                    $allSameContact = count($recentRecords) > 0
                        && count(array_unique($recentRecords)) === 1
                        && (int) $recentRecords[0] === (int) $lastContact->id;

                    $consecutiveCalls = $allSameContact ? count($recentRecords) : 0;

                    // Still under the cap for this contact — keep returning it
                    if ($consecutiveCalls < $maxCallsForContact) {
                        return $this->formatWhatsAppResponse($lastContact);
                    }
                }

                // Move to the next eligible contact of the same type
                $nextContact = null;

                if ($lastRecord) {
                    $nextContact = WhatsAppContacts::where('id', '>', $lastRecord->whats_app_contacts_id)
                        ->where('type', $type)
                        ->whereNotNull('counter')
                        ->where('counter', '>', 0)
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->first();
                }

                // Wrap around to the first eligible contact if none found after the last one
                if (!$nextContact) {
                    $nextContact = WhatsAppContacts::where('type', $type)
                        ->whereNotNull('counter')
                        ->where('counter', '>', 0)
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->first();
                }

                if (!$nextContact) {
                    throw new \RuntimeException("No {$type} contacts found", 404);
                }

                return $this->formatWhatsAppResponse($nextContact);
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 404);
        } catch (\Exception $e) {
            Log::error('getNextWhatsAppNumberByType failed', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'An error occurred while fetching the next ' . $type . ' contact',
            ], 500);
        }
    }

    /**
     * Format the response for next WhatsApp number
     */
    private function formatWhatsAppResponse(WhatsAppContacts $contact)
    {
        return response()->json([
            'next_whatsapp_number' => $contact->phone,
            'contact_id' => $contact->id,
            'counter' => $contact->counter ?? 0,
        ]);
    }

    /**
     * record a click on phone number
     */

    public function recordWhatsAppNumber(Request $request, WhatsAppContacts $whatsapp_contact)
    {
        $whatsapp_contact->records()->create([
            'whats_app_contacts_id' => $whatsapp_contact->id,
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
            'counter' => 'nullable|numeric',
            'type' => 'nullable|in:junior,senior',
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
            'counter' => 'nullable|numeric',
            'type' => 'nullable|in:junior,senior',
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

    /**
     * Get all whatsapp records
     */
    public function showWhatsAppRecords()
    {
        return WhatsAppRecord::with('whatsAppContact')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
