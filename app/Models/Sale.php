<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    public function store(){
        return $this->belongsTo(Store::class);
    }

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function created_by_user(){
        return $this->belongsTo(User::class,'created_by_user_id');
    }
}
