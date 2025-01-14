<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'total_amount',
        'status',
        'user_id',
        'payment_method_id',
        'transaction_code',
        'service_id',
        'created_at',
        'updated_at'
    ];

    public function payment_method()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function order_status()
    {
        return $this->belongsTo(OrderStatus::class, 'uuid', 'uuid');
    }
}
