<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    //

    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'street_address', 
        'district', 
        'upazila', 
        'city',  
        'zip_code',  
        'country',  
        'notes',  
        'verified_at',   
    ];

    public function orders ()
    {
        return $this->hasMany(Order::class);
    }
}