<?php

namespace App\Http\Controllers\Common;


use Illuminate\Http\Request;
use App\Helpers\ErrorTryCatch;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;

class CommonController extends Controller
{
    public function updateStatus(Request $request)
    {
        $data = DB::table($request->tableName)->where('id', $request->id)
        ->update(['status'=>$request->status]);
        if ($data) {
            return 1;
        }
        return 0;
    }

    public function SaleRelationData(Request $request)
    {
        $store_id = $request->store_id;
        $product_id = $request->current_product_id;

        $sale_price = Stock::join('purchases', 'stocks.purchase_id', '=', 'purchases.id')
            ->where('stocks.store_id',$store_id)->where('stocks.product_id',$product_id)
            ->orderBy('stocks.id','DESC')
            ->pluck('stocks.sale_price')
            ->first();

        // stock
        $total_purchase_qty = 20;
        // purchase return
        $total_purchase_return_qty = 0;
        // sale product
        $total_product_sale_qty = 4;
        // sale package
        $total_package_sale_qty = 0;
        // sale return
        $total_sale_return_qty = 0;

        $total_sale_qty=$total_product_sale_qty+ $total_package_sale_qty;
        $purchase_sale_return_qty=($total_sale_qty-$total_sale_return_qty)+$total_purchase_return_qty;
        $current_stock = ($total_purchase_qty-$purchase_sale_return_qty);

        $options = [
            'sale_price' => $sale_price,
            'current_stock' => $current_stock,
            'unitOptions' => '',
        ];

        $unit_id = Product::where('id',$product_id)->pluck('unit_id')->first();
        if($unit_id){
            $units = Unit::where('id',$unit_id)->get();
            if(count($units) > 0){
                $options['unitOptions'] = "<select class='form-control' name='unit_id[]' readonly>";
                foreach($units as $unit){
                    $options['unitOptions'] .= "<option value='$unit->id'>$unit->name</option>";
                }
                $options['unitOptions'] .= "</select>";
            }
        }else{
            $options['unitOptions'] = "<select class='form-control' name='unit_id[]' readonly>";
            $options['unitOptions'] .= "<option value=''>No Data Found!</option>";
            $options['unitOptions'] .= "</select>";
        }

        return response()->json(['success'=>true,'data'=>$options]);
    }
}
