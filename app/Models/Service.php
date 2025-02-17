<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'services';

    public function service_price()
    {
        return $this->hasMany(ServicePrice::class);
    }

    public function transactions() {
        return $this->hasMany(Transaction::class);
    }
}
