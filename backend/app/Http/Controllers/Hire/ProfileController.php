<?php

namespace App\Http\Controllers\Hire;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hire\ProfileRequest;
use App\Http\Resources\BusinessProfileResource;
use App\Models\BusinessProfile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $profile = BusinessProfile::where('user_id', $request->user()->id)->first();

        if (! $profile) {
            return response()->json(['data' => null]);
        }

        return new BusinessProfileResource($profile);
    }

    public function update(ProfileRequest $request)
    {
        $profile = BusinessProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'business_name' => $request->business_name,
                'category' => $request->category,
                'phone' => $request->phone,
            ]
        );

        $request->user()->update(['name' => $request->business_name]);

        return response()->json(['data' => new BusinessProfileResource($profile)])->setStatusCode(200);
    }
}
