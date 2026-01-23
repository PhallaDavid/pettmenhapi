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


    public function index(Request $request)
    {
        $query = Patient::with(['diseaseCategory', 'employee:id,name,position']);

        // Search by registration date (created_at)
        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Search by next visit date
        if ($request->has('date_come_again')) {
            $query->whereDate('date_come_again', $request->date_come_again);
        }

        // Search by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $patients = $query->latest()->paginate(10);
        return response()->json($patients, 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'fullname' => 'required|string|max:255',
            'age' => 'nullable|integer',
            'gender' => 'nullable|string',
            'old' => 'nullable|boolean',
            'phone_number' => 'nullable|string|max:20',
            'select_disease' => 'nullable|exists:disease_categories,id',
            'select_employee' => 'nullable|exists:employees,id',
            'address' => 'nullable|string',
            'date_come_again' => 'nullable|date',
            'status' => 'nullable|string',
        ]);

        // Map input to database fields
        $data = $validatedData;
        if (isset($data['old'])) {
            $data['is_old_patient'] = $data['old'];
            unset($data['old']);
        }
        if (isset($data['select_disease'])) {
            $data['disease_category_id'] = $data['select_disease'];
            unset($data['select_disease']);
        }
        if (isset($data['select_employee'])) {
            $data['employee_id'] = $data['select_employee'];
            unset($data['select_employee']);
        }

        $patient = Patient::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Patient created successfully',
            'patient' => $patient->load(['diseaseCategory', 'employee:id,name,position'])
        ], 201);
    }

    public function show($id)
    {
        $patient = Patient::with(['diseaseCategory', 'employee:id,name,position'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'patient' => $patient
        ]);
    }

    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $validatedData = $request->validate([
            'fullname' => 'sometimes|required|string|max:255',
            'age' => 'nullable|integer',
            'gender' => 'nullable|string',
            'old' => 'nullable|boolean',
            'phone_number' => 'nullable|string|max:20',
            'select_disease' => 'nullable|exists:disease_categories,id',
            'select_employee' => 'nullable|exists:employees,id',
            'address' => 'nullable|string',
            'date_come_again' => 'nullable|date',
            'status' => 'nullable|string',
        ]);

        // Map input to database fields
        $data = $validatedData;
        if (isset($data['old'])) {
            $data['is_old_patient'] = $data['old'];
            unset($data['old']);
        }
        if (isset($data['select_disease'])) {
            $data['disease_category_id'] = $data['select_disease'];
            unset($data['select_disease']);
        }
        if (isset($data['select_employee'])) {
            $data['employee_id'] = $data['select_employee'];
            unset($data['select_employee']);
        }

        $patient->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Patient updated successfully',
            'patient' => $patient->load(['diseaseCategory', 'employee:id,name,position'])
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
