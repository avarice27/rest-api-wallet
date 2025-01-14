<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePrice extends Model
{
    protected $table  = 'service_prices';


    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

}
