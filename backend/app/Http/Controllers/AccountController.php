<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\ChangePasswordRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\VerificationResource;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user()->load(['businessProfile', 'verification']);

        return response()->json(['user' => new UserResource($user)]);
    }

    public function verificationStatus(Request $request)
    {
        $verification = Verification::where('user_id', $request->user()->id)
            ->latest('id')
            ->first();

        return response()->json([
            'data' => $verification ? new VerificationResource($verification) : null,
        ]);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => ['current_password' => ['Password lama tidak sesuai.']],
            ], 422);
        }

        $user->update(['password' => $request->password]);

        return response()->json(['message' => 'Kata sandi berhasil diubah.']);
    }
}