<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Product extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    public function category(){
        return $this->belongsTo(Category::class,'category_id');
    }

    public function brand(){
        return $this->belongsTo(Brand::class);
    }

    public function created_by_user(){
        return $this->belongsTo(User::class,'created_by_user_id');
    }

    public function unit(){
        return $this->belongsTo(Unit::class);
    }

    public function stocktransferrequestdetail(){
        return $this->hasMany(StockTransferRequestDetail::class);
    }
    public function stocktransferdetails(){
        return $this->hasMany(StockTransferDetails::class);
    }
    public function vancurrentstock(){
        return $this->hasMany(VanCurrentStock::class);
    }
    public function productprice(){
        return $this->hasMany(ProductPrice::class);
    }
    public function product_department(){
        return $this->belongsTo(ProductDepartment::class,'product_department_id');
    }
    public function product_section(){
        return $this->belongsTo(ProductSection::class,'product_section_id');
    }
}
