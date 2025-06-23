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

    public function address(): hasOne
    {
        return $this->hasOne(Address::class);
    }
}