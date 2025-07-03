<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;

class SchoolSettingController extends Controller
{
    public function edit()
    {
        // Use firstOrCreate to ensure a settings record always exists in the database
        $settings = SchoolSetting::firstOrCreate(['id' => 1]);
        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = SchoolSetting::firstOrCreate(['id' => 1]);

        $validated = $request->validate([
            'school_name' => 'nullable|string|max:255',
            'school_email' => 'nullable|email|max:255',
            'school_phone' => 'nullable|string|max:255',
            'school_address' => 'nullable|string',
            'current_academic_year' => 'nullable|string|max:255',
            'current_term_semester' => 'nullable|string|max:255',
            'school_logo_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'remove_school_logo' => 'nullable|boolean',
        ]);

        // Handle file upload if a new logo is provided
        if ($request->hasFile('school_logo_file')) {
            $path = $request->file('school_logo_file')->store('school_assets/logos', 'public');
            $validated['school_logo_path'] = $path;
        }

        // Handle logo removal if the checkbox is checked
        if ($request->boolean('remove_school_logo')) {
            $validated['school_logo_path'] = null;
        }

        $settings->update($validated);

        return back()->with('success', 'Settings updated successfully.');
    }
}