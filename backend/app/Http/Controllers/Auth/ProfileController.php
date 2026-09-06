<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\BusinessProfile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();

        $attributes = [];
        foreach (['name', 'email', 'phone'] as $field) {
            if ($request->filled($field)) {
                $attributes[$field] = $request->input($field);
            }
        }

        $photo = $request->file('photo') ?? $request->file('avatar');
        if ($photo) {
            $attributes['avatar'] = $photo->store('avatars', 'public');
        }

        if ($attributes) {
            $user->update($attributes);
        }

        if ($user->role === 'hirer' && ($request->filled('business_name') || $request->hasFile('store_photo'))) {
            $this->updateBusinessProfile($user, $request);
        }

        return response()->json([
            'user' => new UserResource($user->fresh(['businessProfile'])),
        ]);
    }

    private function updateBusinessProfile($user, Request $request): void
    {
        $name = $request->input('business_name', $user->name);
        $category = $request->input('category', $request->input('business_category', 'Kuliner / F&B'));
        $businessType = $request->input('business_type', $category);
        $address = $request->input('address', $request->input('business_address'));

        $profile = BusinessProfile::where('user_id', $user->id)->first();

        if (! $profile) {
            $profile = new BusinessProfile(['user_id' => $user->id]);
        }

        $storePhoto = $profile->store_photo;
        if ($request->hasFile('store_photo')) {
            $storePhoto = $request->file('store_photo')->store('business-profile', 'public');
        }

        $profile->fill([
            'business_name' => $name ?: $profile->business_name,
            'category' => $category ?: ($profile->category ?? 'Kuliner / F&B'),
            'phone' => $request->input('phone', $profile->phone ?? ''),
            'address' => $address ?: $profile->address,
            'business_type' => $businessType ?: $profile->business_type,
            'store_photo' => $storePhoto,
        ])->save();

        $user->update(['name' => $name ?: $user->name]);
    }
}