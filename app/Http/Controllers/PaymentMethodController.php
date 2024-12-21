<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $banks = PaymentMethod::where('status', 'active')
                            ->where('code' , '!=', 'bwa')
                            ->get()
                            ->map(function ($item) {
                                $item->thumbnail = $item->thumbnail ? url('banks/'.$item->thumbnail) : "";
                                return $item;
                            });

        return response()->json($banks);
    }
}
