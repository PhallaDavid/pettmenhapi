<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checkout;
use App\Models\Patient;
use App\Models\DiseaseCategory;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    /**
     * List all checkouts
     */
    public function index(Request $request)
    {
        $query = Checkout::with([
            'patient:id,fullname,phone_number',
            'diseaseCategory:id,name,price',
            'employee:id,name,position'
        ]);

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('checkout_date', $request->date);
        }

        // Filter by patient
        if ($request->has('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by debt (ជំពាក់)
        if ($request->has('is_debt')) {
            if ($request->is_debt == 'true' || $request->is_debt == 1) {
                $query->where('debt_amount', '>', 0);
            } else {
                $query->where('debt_amount', 0);
            }
        }

        $checkouts = $query->latest()->paginate(15);
        
        return response()->json($checkouts, 200, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Perform patient checkout
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'disease_category_id' => 'nullable|exists:disease_categories,id',
            'employee_id' => 'nullable|exists:employees,id',
            'discount_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|in:cash,bank_transfer,e-wallet',
            'notes' => 'nullable|string',
            'checkout_date' => 'nullable|date',
        ]);

        $patient = Patient::findOrFail($validated['patient_id']);
        
        // Default checkout date to today (Cambodia time)
        $checkout_date = $validated['checkout_date'] ?? Carbon::now('Asia/Phnom_Penh')->toDateString();

        $total_amount = 0;
        $disease_id = $validated['disease_category_id'] ?? $patient->disease_category_id;

        if ($disease_id) {
            $disease = DiseaseCategory::find($disease_id);
            if ($disease) {
                // Determine if there is an active promotion
                $today = Carbon::now('Asia/Phnom_Penh');
                $isPromotionActive = false;
                
                if ($disease->date_start_promotion && $disease->date_end_promotion) {
                    $start = Carbon::parse($disease->date_start_promotion);
                    $end = Carbon::parse($disease->date_end_promotion);
                    if ($today->between($start, $end)) {
                        $isPromotionActive = true;
                    }
                }

                $total_amount = $isPromotionActive ? ($disease->price_after_promotion ?? $disease->price) : $disease->price;
            }
        }

        $discount = $validated['discount_amount'] ?? 0;
        $final_amount = max(0, $total_amount - $discount);

        $paid_amount = $validated['paid_amount'] ?? $final_amount;
        $debt_amount = max(0, $final_amount - $paid_amount);

        // Determine status
        $status = 'paid';
        if ($debt_amount > 0) {
            $status = ($paid_amount > 0) ? 'partial' : 'debt';
        }

        $checkout = Checkout::create([
            'patient_id' => $patient->id,
            'disease_category_id' => $disease_id,
            'employee_id' => $validated['employee_id'] ?? $patient->employee_id,
            'total_amount' => $total_amount,
            'discount_amount' => $discount,
            'final_amount' => $final_amount,
            'paid_amount' => $paid_amount,
            'debt_amount' => $debt_amount,
            'payment_method' => $validated['payment_method'] ?? 'cash',
            'status' => $status,
            'notes' => $validated['notes'],
            'checkout_date' => $checkout_date,
        ]);

        // Send Telegram Alert
        TelegramService::sendCheckoutAlert($checkout);

        return response()->json([
            'success' => true,
            'message' => 'Patient checkout completed successfully',
            'checkout' => $checkout->load(['patient', 'diseaseCategory', 'employee:id,name,position'])
        ], 201);
    }

    /**
     * Show checkout details
     */
    public function show(Checkout $checkout)
    {
        return response()->json([
            'success' => true,
            'checkout' => $checkout->load(['patient', 'diseaseCategory', 'employee:id,name,position'])
        ]);
    }

    /**
     * Delete checkout record
     */
    public function destroy(Checkout $checkout)
    {
        $checkout->delete();
        return response()->json([
            'success' => true,
            'message' => 'Checkout record deleted successfully'
        ]);
    }
}
