<?php

namespace App\Http\Controllers\Hire;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hire\VerificationRequest;
use App\Http\Resources\UserResource;
use App\Models\BusinessProfile;
use App\Models\Verification;

class VerificationController extends Controller
{
    public function store(VerificationRequest $request)
    {
        $user = $request->user();

        $storePhoto = $request->file('store_photo')->store('business-profile', 'public');

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'is_verified' => false,
        ]);

        $profile = BusinessProfile::where('user_id', $user->id)->first();

        if (! $profile) {
            $profile = new BusinessProfile(['user_id' => $user->id]);
        }

        $profile->fill([
            'business_name' => $request->business_name,
            'category' => $request->business_type,
            'business_type' => $request->business_type,
            'address' => $request->address,
            'phone' => $request->phone,
            'store_photo' => $storePhoto,
        ])->save();

        Verification::updateOrCreate(
            ['user_id' => $user->id, 'status' => 'pending'],
            [
                'role' => 'hirer',
                'status' => 'pending',
                'data' => [
                    'business_name' => $profile->business_name,
                    'category' => $profile->category,
                    'business_type' => $profile->business_type,
                    'address' => $profile->address,
                    'phone' => $profile->phone,
                    'photo_path' => $storePhoto,
                    'note' => 'Foto tampak depan kedai dan plang nama toko.',
                ],
            ]
        );

        return response()->json([
            'message' => 'Data verifikasi berhasil dikirim! Verifikasi sedang diproses.',
            'user' => new UserResource($user->fresh(['businessProfile', 'verification'])),
        ]);
    }
}