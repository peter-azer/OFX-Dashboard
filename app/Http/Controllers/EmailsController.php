<?php

namespace App\Http\Controllers;

use App\Models\Emails;
use App\Models\Service;
use App\Http\Requests\StoreEmailsRequest;
use App\Http\Requests\UpdateEmailsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class EmailsController extends Controller
{
    /**
     * Display a listing of the email addresses.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $emails = Emails::with('services')
                       ->orderBy('is_main', 'desc')
                       ->orderBy('created_at', 'desc')
                       ->get();
        $services = Service::select('id', 'service_name')->get();
        return response()->json([
            'success' => true,
            'data' => $emails,
            'services' => $services
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
     * Store a newly created email in storage.
     *
     * @param  \App\Http\Requests\StoreEmailsRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|email',
                'service_ids' => 'sometimes|array',
                'service_ids.*' => 'exists:services,id',
            ]);
            
            DB::beginTransaction();
            
            $email = Emails::create($validatedData);
            
            // Attach services if provided
            if (isset($validatedData['service_ids'])) {
                $email->services()->sync($validatedData['service_ids']);
            }
            
            DB::commit();
            
            // Load the services relationship for the response
            $email->load('services');
            
            return response()->json([
                'success' => true,
                'message' => 'Email added successfully',
                'data' => $email
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified email.
     *
     * @param  \App\Models\Emails  $email
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Emails $email): JsonResponse
    {
        // Load the services relationship
        $email->load('services');
        
        return response()->json([
            'success' => true,
            'data' => $email
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Emails $emails)
    {
        //
    }

    /**
     * Update the specified email in storage.
     *
     * @param  \App\Http\Requests\UpdateEmailsRequest  $request
     * @param  \App\Models\Emails  $email
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Emails $email)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|email',
                'service_ids' => 'sometimes|array',
                'service_ids.*' => 'exists:services,id',
            ]);
            
            DB::beginTransaction();
            
            $email->update($validatedData);
            
            // Sync services if provided
            if (isset($validatedData['service_ids'])) {
                $email->services()->sync($validatedData['service_ids']);
            }
            
            DB::commit();
            
            // Load the services relationship for the response
            $email->load('services');
            
            return response()->json([
                'success' => true,
                'message' => 'Email updated successfully',
                'data' => $email
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified email from storage.
     *
     * @param  \App\Models\Emails  $email
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Emails $email): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            // Detach all services before deleting the email
            $email->services()->detach();
            
            // Delete the email
            $email->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Email deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete email',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mark an email as the main email address.
     *
     * @param  \App\Models\Emails  $email
     * @return \Illuminate\Http\JsonResponse
     */
    public function setAsMain(Emails $email): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            // First, set all other emails to not be main
            Emails::where('is_main', true)->update(['is_main' => false]);
            
            // Set the selected email as main
            $email->is_main = true;
            $email->save();
            
            DB::commit();
            
            // Load the services relationship for the response
            $email->load('services');
            
            return response()->json([
                'success' => true,
                'message' => 'Email set as main successfully',
                'data' => $email
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to set email as main',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Toggle the active status of an email.
     *
     * @param  \App\Models\Emails  $email
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus(Request $request, Emails $email): JsonResponse
    {
        try {
            $validated = $request->validate([
                'is_active' => 'required|boolean'
            ]);
            
            $email->update([
                'is_active' => $validated['is_active']
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Email status updated successfully',
                'data' => $email
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update email status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
