<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Freelancer\VerificationRequest;
use App\Models\Verification;

class VerificationController extends Controller
{
    public function store(VerificationRequest $request)
    {
        $user = $request->user();

        $user->update([
            'phone' => $request->input('phone', $user->phone),
            'email' => $request->input('email', $user->email),
            'is_verified' => false,
        ]);

        Verification::updateOrCreate(
            ['user_id' => $user->id, 'status' => 'pending'],
            [
                'role' => 'freelancer',
                'status' => 'pending',
                'data' => [
                    'bank_name' => $request->bank_name,
                    'account_number' => $request->account_number,
                    'account_holder_name' => $request->account_holder_name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'note' => 'Verifikasi rekening bank dan data identitas pencairan.',
                ],
            ]
        );

        return response()->json([
            'message' => 'Data verifikasi berhasil dikirim! Verifikasi sedang diproses.',
        ]);
    }
}