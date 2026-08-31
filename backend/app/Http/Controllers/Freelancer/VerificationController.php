<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Freelancer\VerificationRequest;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function store(VerificationRequest $request)
    {
        $user = $request->user();

        $user->update([
            'phone' => $request->get('phone', $user->phone),
            'is_verified' => false,
        ]);

        return response()->json([
            'message' => 'Dokumen KTP berhasil dikirim! Verifikasi sedang diproses.',
        ]);
    }
}
