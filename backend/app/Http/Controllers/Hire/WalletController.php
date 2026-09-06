<?php

namespace App\Http\Controllers\Hire;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hire\TopUpRequest;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        return $this->walletResponse(Wallet::forUser($request->user()->id));
    }

    public function topup(TopUpRequest $request)
    {
        $wallet = DB::transaction(function () use ($request) {
            $wallet = Wallet::where('user_id', $request->user()->id)->lockForUpdate()->first();

            if (!$wallet) {
                $wallet = Wallet::create(['user_id' => $request->user()->id, 'balance' => 0]);
            }

            $wallet->increment('balance', $request->amount);
            $wallet->transactions()->create([
                'type' => WalletTransaction::TYPE_TOPUP,
                'title' => "Top Up Escrow via Transfer {$request->bank_name}",
                'amount' => $request->amount,
            ]);

            return $wallet->fresh();
        });

        return $this->walletResponse($wallet);
    }

    private function walletResponse(Wallet $wallet): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data' => [
                'balance' => $wallet->balance,
                'transactions' => $wallet->transactions()
                    ->orderByDesc('created_at')
                    ->get()
                    ->map(fn (WalletTransaction $tx) => $this->shapeTransaction($tx)),
            ],
        ]);
    }

    private function shapeTransaction(WalletTransaction $tx): array
    {
        return [
            'id' => $tx->id,
            'type' => $tx->type,
            'title' => $tx->title,
            'amount' => $tx->amount,
            'date' => $tx->created_at->toDateString(),
            'month' => $tx->created_at->format('m'),
            'year' => $tx->created_at->format('Y'),
        ];
    }
}