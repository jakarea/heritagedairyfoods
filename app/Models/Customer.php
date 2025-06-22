<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; 

use Illuminate\Database\Eloquent\Relations\hasOne;

class Customer extends Model
{
    //

    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone', 
        'notes',   
        'verified_at',   
    ];

    public function orders ()
    {
        return $this->hasMany(Order::class);
    }

    

    public function billingAddress(): hasOne
    {
        return $this->hasOne(Address::class)->where('type', 'billing');
    }

    public function shippingAddress(): hasOne
    {
        return $this->hasOne(Address::class)->where('type', 'shipping');
    }
}