<?php

namespace App\Http\Controllers;

use App\Models\FormSubmition;
use App\Models\Emails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FormSubmitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $submissions = FormSubmition::with('services')->latest()->get();
        return response()->json($submissions);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // This method is typically used to show the form for creating a new resource
        // In an API context, this might not be needed
        return response()->json(['message' => 'Use POST /form-submissions to create a new submission']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'message' => 'nullable|string',
                'services' => 'nullable|array',
                'services.*' => 'exists:services,id',
            ]);

            // Start database transaction for data consistency
            return \DB::transaction(function () use ($validated) {
                // Create submission
                $submission = FormSubmition::create([
                    'full_name' => $validated['full_name'],
                    'phone_number' => $validated['phone_number'],
                    'email' => $validated['email'],
                    'message' => $validated['message'] ?? null,
                ]);

                // Attach services if any
                if (!empty($validated['services'])) {
                    $submission->services()->sync($validated['services']);
                }

                // Get all relevant emails in a single optimized query
                $emails = Emails::where(function($query) use ($validated) {
                        // Main active emails
                        $query->where('is_main', true)
                              ->where('is_active', true);
                        
                        // OR service-specific emails if services are provided
                        if (!empty($validated['services'])) {
                            $query->orWhereHas('services', function($q) use ($validated) {
                                $q->whereIn('services.id', $validated['services'])
                                  ->where('is_active', true);
                            });
                        }
                    })
                    ->distinct()
                    ->get();

                // Log the submission with minimal details
                Log::info("New submission #{$submission->id} from {$submission->email}");

                // Send notification to each email with error handling
                $notification = new \App\Notifications\FormSubmition($submission->toArray());
                $failedRecipients = [];
                
                foreach ($emails as $email) {
                    try {
                        $email->notify($notification);
                    } catch (\Exception $e) {
                        $failedRecipients[] = $email->email;
                        Log::error("Failed to send email to {$email->email}: " . $e->getMessage());
                    }
                }
                
                if (!empty($failedRecipients)) {
                    Log::warning("Failed to send to some recipients for submission #{$submission->id}", [
                        'failed_recipients' => $failedRecipients
                    ]);
                } else {
                    Log::info("Successfully sent notifications for submission #{$submission->id}");
                }

                return response()->json([
                    'message' => 'Form submitted successfully',
                    'data' => $submission->load('services'),
                    'notified_emails' => $emails->pluck('email')
                ], 201);
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Form submission failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to process your request. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FormSubmition $formSubmition)
    {
        return response()->json($formSubmition->load('services'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FormSubmition $formSubmition)
    {
        // This method is typically used to show the form for editing the specified resource
        // In an API context, we'll just return the resource data
        return response()->json($formSubmition->load('services'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FormSubmition $formSubmition)
    {
        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'message' => 'sometimes|string',
            'services' => 'sometimes|array',
            'services.*' => 'exists:services,id',
        ]);

        $formSubmition->update([
            'full_name' => $validated['full_name']  ?? $formSubmition->full_name,
            'phone_number' => $validated['phone_number'] ?? $formSubmition->phone_number,
            'email' => $validated['email'] ?? $formSubmition->email,
            'message' => $validated['message'] ?? $formSubmition->message,
        ]);

        if (isset($validated['services']) && is_array($validated['services'])) {
            $formSubmition->services()->sync($validated['services']);
        }

        return response()->json([
            'message' => 'Form submission updated successfully',
            'data' => $formSubmition->load('services')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FormSubmition $formSubmition)
    {
        // Detach all services first
        $formSubmition->services()->detach();

        // Then delete the submission
        $formSubmition->delete();

        return response()->json([
            'message' => 'Form submission deleted successfully'
        ], 204);
    }
}
