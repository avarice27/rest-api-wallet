<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class ServiceController extends Controller
{
    public function buyServices(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'service_id' => 'required|integer',
            'pin' => 'required|digits:6',
            'bank' => 'required|in:bni_va,bca_va,bri_va'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'data' => [
                    'message' => $validator->messages()->first()
                ]
            ], 422);
        }

        // get current user
        $user = auth('api')->user();

        if ($request->user_id != $user->uuid) {
            return response()->json([
                'error' => true,
                'data' => [
                    'message' => 'Forbidden access.'
                ]
            ], 401);
        }

        // get services
        $service = Service::find($request->service_id);

        if (!$service) {
            return response()->json([
                'error' => true,
                'data' => [
                    'message' => "Service not found."
                ]
            ], 422);
        }

        // Check PIN
        $pinChecker = pinChecker($request->pin);
        if (!$pinChecker) {
            return response()->json([
                'error' => true,
                'data' => [
                    'message' => 'Your PIN is wrong'
                ]
            ], 400);
        }

        // Get user wallet and service price
        $userWallet = Wallet::where('user_id', $user->uuid)->first();
        $servicePrice = ServicePrice::where('role_id', $user->role_id)
            ->where('service_id', $request->service_id)
            ->first();

        if (!$servicePrice) {
            return response()->json([
                'error' => true,
                'data' => [
                    'message' => 'Service price not found for your role.'
                ]
            ], 404);
        }

        // Check wallet balance
        if ($userWallet->balance < $servicePrice->price) {
            return response()->json([
                'error' => true,
                'data' => [
                    'message' => 'Insufficient wallet balance.'
                ]
            ], 400);
        }

        try {
            DB::beginTransaction();

            $orderId = Str::uuid()->toString();
            $paymentMethod = PaymentMethod::where('code', 'bdm')->first();

            // Create transaction
            $transaction = Transaction::create([
                'uuid' => $orderId,
                'payment_method_id' => $paymentMethod->id,
                'total_amount' => $servicePrice->price,
                'user_id' => $request->user_id,
                'service_id' => $request->service_id,
                'transaction_code' => strtoupper(Str::random(10)),
                'description' => 'Service Purchase: ' . $service->name,
                'status' => 'success',
                'created_at' => now()
            ]);

            // Create order status
            OrderStatus::create([
                'uuid' => $orderId,
                'created_at' => now(),
            ]);

            // Deduct from wallet
            $userWallet->decrement('balance', $servicePrice->price);

            DB::commit();

            return response()->json([
                'error' => false,
                'data' => [
                    'message' => 'Service purchase successful',
                    'order_id' => $orderId,
                    'amount' => $servicePrice->price,
                    'service_name' => $service->name,
                    'transaction_status' => 'success'
                ]
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'data' => [
                    'message' => $e->getMessage(),
                ]
            ], 500);
        }
    }

    public function getServicesWithPrices()
    {
        $servicesPrices = ServicePrice::get();

        if($servicesPrices->isNotEmpty()) {
            return response()->json([
                'error' => false,
                'data' => [
                    'message' => 'Success get all services',
                    'services' => $servicesPrices->map(function($v) {
                        return [
                            'name' => $v->service->name,
                            'price' => $v->price,
                            'role' => $v->role->name,
                            'last_updated' => $v->updated_at
                        ];
                    })
                ]
            ]);
        }

        return response()->json([
            'error' => true,
            'data' => [
                'message' => 'No services found'
            ]
        ], 404);
    }
}
