<?php

namespace App\Http\Controllers\Common;


use Illuminate\Http\Request;
use App\Helpers\ErrorTryCatch;
use App\Helpers\Helper;
use App\Models\Customer;
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

    public function PurchaseRelationData(Request $request)
    {
        $product_id = $request->current_product_id;

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

    public function SaleRelationData(Request $request)
    {
        $store_id = $request->store_id;
        $product_id = $request->current_product_id;

        $sale_price = Stock::join('purchases', 'stocks.purchase_id', '=', 'purchases.id')
            ->where('stocks.store_id',$store_id)->where('stocks.product_id',$product_id)
            ->orderBy('stocks.id','DESC')
            ->pluck('stocks.sale_price')
            ->first();

        $options = [
            'sale_price' => $sale_price,
            'current_stock' => Helper::storeProductCurrentStock($store_id, $product_id),
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

    // public function FindProductInfo(Request $request)
    // {
    //     if ($request->has('q')) {
    //         $data = DB::table('products')
    //             ->join('stocks', 'stocks.product_id', '=', 'products.id')
    //             ->where(function ($query) use ($request) {
    //                 $query->where('products.name', 'like', '%' . $request->q . '%');
    //             })
    //             ->where('products.status', '=', 1)
    //             ->select('products.name', 'products.barcode', 'products.id',  'products.status', 'products.unit_id', 'products.vat_id')
    //             ->get();

    //         if ($data) {
    //             return response()->json($data);
    //         } else {
    //             return response()->json(['success' => false, 'customer' => 'Error!!']);
    //         }
    //     }
    // }

    public function FindProductInfo(Request $request)
    {
        if ($request->has('q')) {
            $data = DB::table('products')
                ->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->q . '%');
                })
                ->where('status', '=', 1)
                ->select('name', 'barcode', 'id',  'status', 'unit_id', 'vat_id')
                ->get();

            if ($data) {
                return response()->json($data);
            } else {
                return response()->json(['success' => false, 'customer' => 'Error!!']);
            }
        }
    }

    public function FindCustomerInfo(Request $request)
    {
        return Customer::select('id', 'name')->get();
    }
}
