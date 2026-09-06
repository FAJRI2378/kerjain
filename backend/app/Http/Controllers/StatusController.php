<?php

namespace App\Http\Controllers;

use App\Models\Setting;

class StatusController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => [
                'maintenance_mode' => filter_var(Setting::get('maintenance_mode', false), FILTER_VALIDATE_BOOLEAN),
                'message' => 'Kami sedang melakukan perbaikan dan pemeliharaan sistem. Mohon kembali beberapa saat lagi.',
            ],
        ]);
    }
}