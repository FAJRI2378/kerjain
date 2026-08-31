<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

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

        $users = $query->orderByDesc('created_at')->paginate($request->get('per_page', 20));

        return UserResource::collection($users);
    }

    public function verify(User $user)
    {
        $user->update(['is_verified' => ! $user->is_verified]);

        return response()->json(['message' => 'Verification status updated.']);
    }

    public function suspend(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);

        return response()->json(['message' => 'Account status updated.']);
    }
}
