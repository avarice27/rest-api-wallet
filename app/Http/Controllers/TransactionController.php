<?php

namespace App\Http\Controllers;

use App\Models\Transaction;

class TransactionController extends Controller
{
    public function getAllTransactions() {
        $user = auth('api')->user();
        $transaction = Transaction::where('user_id', $user->uuid)->get();

        if ($user->role->name === "admin") {
            $transaction = Transaction::all();
        }

        return response()->json([
            'error' => false,
            'data' => [
                'message' => "Success get all transaction data",
                'transactions' => $transaction->map(function ($v) {
                    return [
                        'uuid' => $v->uuid,
                        'total_amount' => $v->total_amount,
                        // 'order_status' => $v->order_status->status,
                        'created_at' => $v->created_at,
                        'updated_at' => $v->updated_at,
                        'payment_method' => $v->payment_method->name,
                        // 'service' => $v->service->name,
                    ];
                }),
            ]
        ]);
    }
}
