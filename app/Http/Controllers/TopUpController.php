<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Models\TransactionType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Wallet;
class TopUpController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->only('amount', 'pin', 'payment_method_code');
        $validator = Validator::make($data, [
           'amount' => 'required|integer|min:10000',
           'pin' => 'required|digits:6',
           'payment_method_code' => 'required|in:bni_va,bca_va,bri_va'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        $pinChecker = pinChecker($request->pin);
        if (!$pinChecker) {
            return response()->json(['message' => 'Your PIN is wrong'], 400);
        }

        // $transactionType = TransactionType::where('code', 'top_up')->first();
        $paymentMethod = PaymentMethod::where('code', $request->payment_method_code)->first();
// dd($paymentMethod->id);
        DB::beginTransaction();

        try {
            $user = auth()->user();
            $uuid = Str::uuid();
            $transaction = Transaction::create([
                'uuid' => $uuid,
                'payment_method_id' => $paymentMethod->id,
                // 'transaction_type_id' => $transactionType->id,
                'transaction_code' => strtoupper(Str::random(10)),
                'total_amount' => $request->amount,
                'description' => 'Top Up via '.$paymentMethod->name,
                'user_id' => auth()->user()->uuid,
                // 'status' => 'pending',
                'service_id' => '1',
            ]);

            $params = $this->buildMidtransParameter([
                'transaction_code' => $transaction->transaction_code,
                'amount' => $transaction->total_amount,
                'payment_method' => $paymentMethod->code
            ]);

            $midtrans = $this->callMidtrans($params);

            $currentBalance = Wallet::select('pin', 'balance', 'card_number')->where('user_id', $user->uuid)->first();

            Wallet::where('user_id', $user->uuid)
                ->update(['balance' => $currentBalance->balance += $request->amount]);

            DB::commit();

            return response()->json($midtrans);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    private function callMidtrans(array $params)
    {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION', false);
        \Midtrans\Config::$isSanitized = (bool) env('MIDTRANS_IS_SANITIZED', true);
        \Midtrans\Config::$is3ds = (bool) env('MIDTRANS_IS_3DS', true);

        $createTransaction = \Midtrans\Snap::createTransaction($params);

        return [
            'redirect_url' => $createTransaction->redirect_url,
            'token' => $createTransaction->token
        ];
    }

    private function buildMidtransParameter(array $params)
    {
        $transactionDetails = [
            'order_id' => $params['transaction_code'],
            'gross_amount' => $params['amount']
        ];

        $user = auth()->user();
        $splitName = $this->splitName($user->name);
        $customerDetails = [
            'first_name' => $splitName['first_name'],
            'last_name' => $splitName['last_name'],
            'email' => $user->email
        ];

        $enabledPayments = [
            $params['payment_method']
        ];

        return [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'enabled_payments' => $enabledPayments
        ];
    }

    private function splitName($fullName)
    {
        $name = explode(' ', $fullName);

        $lastName = count($name)  > 1 ? array_pop($name) : $fullName;
        $firstName = implode(' ', $name);

        return [
            'first_name' => $firstName,
            'last_name' => $lastName
        ];
    }
}
