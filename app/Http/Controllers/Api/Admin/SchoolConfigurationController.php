<?php

namespace App\Http\Controllers\Api\Admin; // Ensure namespace is correct

use App\Http\Controllers\Controller;
use App\Models\SchoolConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage; // For logo upload later

class SchoolConfigurationController extends Controller
{
    /**
     * Display the current school configuration.
     * There should ideally be only one record, or a way to get the active one.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        // Use the static method from the model, or fetch the first record.
        // The current() method in the model ensures one is created if it doesn't exist.
        $configuration = SchoolConfiguration::current(); 
                                           
        if (!$configuration) {
            // This case should ideally be handled by the current() method creating a default,
            // but as a fallback:
            return response()->json(['message' => 'School configuration not found. Please set it up.'], 404);
        }
        return response()->json($configuration);
    }

    /**
     * Update the school configuration.
     * Assumes a single configuration record (e.g., id=1) or updates the first one found.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        // Fetch the existing configuration record, or create a default one if it doesn't exist
        $configuration = SchoolConfiguration::firstOrCreate(
            ['id' => 1], // Assuming ID 1 is the designated record
            [ /* Default values if creating, though seeder should handle this */
                'school_name' => 'My School', 
                'app_timezone' => 'UTC'
            ]
        );

        $validator = Validator::make($request->all(), [
            'school_name' => 'sometimes|nullable|string|max:255',
            'school_address' => 'sometimes|nullable|string',
            'school_phone' => 'sometimes|nullable|string|max:50',
            'school_email' => 'sometimes|nullable|email|max:255',
            'current_academic_year' => 'sometimes|nullable|string|max:20',
            'current_term_semester' => 'sometimes|nullable|string|max:50',
            'school_logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // For new logo upload
            'date_format' => 'sometimes|nullable|string|max:20',
            'app_timezone' => 'sometimes|nullable|string|max:100',
            'currency_symbol' => 'sometimes|nullable|string|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        
        // Handle logo upload
        if ($request->hasFile('school_logo_file')) {
            // Delete old logo if it exists
            if ($configuration->school_logo_path && Storage::disk('public')->exists($configuration->school_logo_path)) {
                Storage::disk('public')->delete($configuration->school_logo_path);
            }
            // Store new logo
            $logoPath = $request->file('school_logo_file')->store('school_assets/logos', 'public');
            $validatedData['school_logo_path'] = $logoPath;
        } elseif ($request->has('remove_school_logo') && $request->input('remove_school_logo')) {
            // If a flag is sent to remove the logo
             if ($configuration->school_logo_path && Storage::disk('public')->exists($configuration->school_logo_path)) {
                Storage::disk('public')->delete($configuration->school_logo_path);
            }
            $validatedData['school_logo_path'] = null;
        }


        $configuration->update($validatedData);

        return response()->json([
            'message' => 'School configuration updated successfully.',
            'configuration' => $configuration->fresh() // Return the updated model
        ]);
    }
}