<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VerifyRejectRequest;
use App\Http\Resources\VerificationResource;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    public function index(Request $request)
    {
        $verifications = Verification::with('user')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->input('role')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->whereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            })
            ->latest('created_at')
            ->paginate((int) $request->input('per_page', 12));

        return VerificationResource::collection($verifications);
    }

    public function pending()
    {
        $verifications = Verification::with('user')
            ->where('status', 'pending')
            ->latest('created_at')
            ->get();

        return VerificationResource::collection($verifications);
    }

    public function approve(Verification $verification)
    {
        if ($verification->status !== 'pending') {
            return response()->json(['message' => 'This verification is already processed.'], 409);
        }

        try {
            DB::transaction(function () use ($verification) {
                $verification->update([
                    'status' => 'approved',
                    'reviewed_by' => request()->user()->id,
                    'reviewed_at' => now(),
                ]);

                $verification->user()->update(['is_verified' => true]);
            });
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to approve verification.'], 500);
        }

        return response()->json(['message' => 'Verification approved successfully.']);
    }

    public function reject(VerifyRejectRequest $request, Verification $verification)
    {
        if ($verification->status !== 'pending') {
            return response()->json(['message' => 'This verification is already processed.'], 409);
        }

        $verification->update([
            'status' => 'rejected',
            'admin_note' => $request->reason,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return response()->json(['message' => 'Verification rejected.']);
    }
}