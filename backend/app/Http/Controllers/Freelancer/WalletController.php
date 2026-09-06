<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Freelancer\WithdrawRequest;
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

    public function withdraw(WithdrawRequest $request)
    {
        $wallet = DB::transaction(function () use ($request) {
            $wallet = Wallet::where('user_id', $request->user()->id)->lockForUpdate()->first();

            if (!$wallet) {
                $wallet = Wallet::create(['user_id' => $request->user()->id, 'balance' => 0]);
            }

            if ($wallet->balance < $request->amount) {
                abort(409, 'Saldo dompet tidak mencukupi untuk penarikan ini.');
            }

            $wallet->decrement('balance', $request->amount);
            $wallet->transactions()->create([
                'type' => WalletTransaction::TYPE_OUT,
                'title' => "Penarikan Dana ke {$request->bank_name} ({$request->account_number})",
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