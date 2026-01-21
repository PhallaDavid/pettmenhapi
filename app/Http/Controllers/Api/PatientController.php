<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index()
    {
        $patients = Patient::paginate(10);
        return response()->json($patients, 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:patients,email',
            'address' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        $patient = Patient::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Patient created successfully',
            'patient' => $patient
        ], 201);
    }

    public function show($id)
    {
        $patient = Patient::findOrFail($id);
        return response()->json([
            'success' => true,
            'patient' => $patient
        ]);
    }

    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:patients,email,' . $id,
            'address' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        $patient->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Patient updated successfully',
            'patient' => $patient
        ]);
    }

    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);
        $patient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Patient deleted successfully'
        ]);
    }
}
