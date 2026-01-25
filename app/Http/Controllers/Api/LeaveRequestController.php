<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\TelegramService;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of leave requests.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $categoryId = $request->query('leave_category_id');
        $userId = $request->query('user_id');

        $query = LeaveRequest::with(['user:id,name,email', 'category', 'reviewer:id,name']);

        if (!$request->user()->can('view all leave requests')) {
            $query->where('user_id', $request->user()->id);
        } elseif ($userId) {
            $query->where('user_id', $userId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($categoryId) {
            $query->where('leave_category_id', $categoryId);
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $leaveRequests
        ]);
    }

    /**
     * Store a newly created leave request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_category_id' => 'required|exists:leave_categories,id',
            'leave_type' => 'required|in:full_day,morning,afternoon,custom',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'required_if:leave_type,custom|nullable|date_format:H:i',
            'end_time' => 'required_if:leave_type,custom|nullable|date_format:H:i|after:start_time',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'start_time.required_if' => 'Start time is required for custom leave type',
            'end_time.required_if' => 'End time is required for custom leave type',
            'end_time.after' => 'End time must be after start time',
        ]);

        // Check if the selected category requires an end date
        $category = \App\Models\LeaveCategory::find($validated['leave_category_id']);
        if ($category && $category->requires_end_date && empty($validated['end_date'])) {
            return response()->json([
                'success' => false,
                'message' => 'End date is required for ' . $category->name . ' category'
            ], 422);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave_attachments', 'public');
        }

        $leaveRequest = LeaveRequest::create([
            'user_id' => $request->user()->id,
            'leave_category_id' => $validated['leave_category_id'],
            'leave_type' => $validated['leave_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'reason' => $validated['reason'],
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        // Send Telegram Alert
        TelegramService::sendLeaveAlert($leaveRequest->load(['user', 'category']), 'new');

        return response()->json([
            'success' => true,
            'message' => 'Leave request submitted successfully',
            'data' => $leaveRequest->load('category')
        ], 201);
    }

    /**
     * Update the status of a leave request.
     */
    public function updateStatus(Request $request, LeaveRequest $leaveRequest)
    {
        if (!$request->user()->can('manage leave requests')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update leave status'
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'review_note' => 'nullable|string',
        ]);

        $leaveRequest->update([
            'status' => $validated['status'],
            'review_note' => $validated['review_note'] ?? null,
            'reviewed_by' => $request->user()->id,
        ]);

        // Send Telegram Alert
        TelegramService::sendLeaveAlert($leaveRequest->load(['user', 'reviewer']), 'update');

        return response()->json([
            'success' => true,
            'message' => 'Leave request status updated to ' . $validated['status'],
            'data' => $leaveRequest
        ]);
    }

    /**
     * Display the specified leave request.
     */
    public function show(LeaveRequest $leaveRequest)
    {
        return response()->json([
            'success' => true,
            'data' => $leaveRequest->load(['user', 'reviewer'])
        ]);
    }
}
