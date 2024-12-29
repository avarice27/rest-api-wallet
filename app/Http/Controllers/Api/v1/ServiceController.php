<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;
use Midtrans;


class ServiceController extends Controller
{
    public function buyServices(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'service_id' => 'required|integer',
            'bank' => 'required|in:bni,bca,bri'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'data' => [
                    'message' => $validator->messages()->first()
                ]
            ], 422);
        }

        // get services
        $services = Service::where('id', $request->service_id)->first();

        if (!$services) {
            return response()->json([
                'error' => true,
                'data' => [
                    'message' => "Service not found, https://kegiatan.upnvj.ac.id/."
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

        try {
            DB::beginTransaction();

            $orderId = Str::uuid()->toString();
            $grossAmount = ServicePrice::where('role_id', $user->role_id)
                ->where('service_id',  $request->service_id)
                ->first();

            $transaction_details = [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount->price
            ];

            $customer_details = [
                'email'            => $user->email,
            ];

            $transaction_data = [
                'payment_type' => 'bank_transfer',
                'transaction_details' => $transaction_details,
                'bank_transfer' => [
                    'bank' => $request->bank
                ],
                'customer_details' => $customer_details
            ];

            $response = \Midtrans\CoreApi::charge($transaction_data);

            // NEED EXCEPTION! MIDTRANS DOESN'T HAS AN FAILED METHOD
            // if ($response->failed()) {
            //     throw new Exception("Simulated API failure");
            // }

            // when payment methods not found
            $paymentsMethods = PaymentMethod::whereCode($request->bank)->first();

            if (!$paymentsMethods) {
                return response()->json([
                    'error' => true,
                    'data' => [
                        'message' => 'Bank ' . $request->bank . ' not found'
                    ]
                ]);
            }

            $trasanctionInsert = Transaction::insert([
                'uuid' => $orderId,
                'payment_method_id' => $paymentsMethods->id,
                'total_amount' => $grossAmount->price,
                'user_id' => $request->user_id,
                'service_id' => $request->service_id,
                'created_at' => now()
            ]);

            if($trasanctionInsert) {
                OrderStatus::insert([
                    'uuid' => $orderId,
                    'created_at' => now()
                ]);
            }
            
            DB::commit();

            return response()->json([
                'error' => false,
                'data' => [
                    'message' => $response->status_message,
                    'order_id' => $response->order_id,
                    'gross_amount' => (int)$response->gross_amount,
                    'transaction_status' => $response->transaction_status,
                    'va_number' => $response->va_numbers[0]->va_number,
                    'bank' => $response->va_numbers[0]->bank
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

    public function getServicesWithPrices() {
        $servicesPrices = ServicePrice::get();
        
        if($servicesPrices->isNotEmpty()) {
            return response()->json([
                'error' => false,
                'data' => [
                    'message' => 'Success get all services',
                    $servicesPrices->map(function($v) {
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
    }

}
