<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', '!=', 'admin');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        $users = $query->with(['businessProfile', 'verification'])->orderByDesc('created_at')->paginate($request->get('per_page', 20));

        return UserResource::collection($users);
    }

    public function verify(User $user)
    {
        DB::transaction(function () use ($user) {
            $user->update(['is_verified' => ! $user->is_verified]);

            if ($user->is_verified) {
                $latest = $user->verifications()->latest('id')->first();

                Verification::updateOrCreate(
                    ['user_id' => $user->id, 'status' => 'approved'],
                    [
                        'role' => $user->role,
                        'status' => 'approved',
                        'data' => array_merge($latest?->data ?? [], ['note' => 'Verifikasi cepat oleh admin.']),
                        'reviewed_by' => request()->user()->id,
                        'reviewed_at' => now(),
                    ]
                );
            }
        });

        return response()->json(['message' => 'Verification status updated.']);
    }

    public function suspend(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);

        return response()->json(['message' => 'Account status updated.']);
    }
}
