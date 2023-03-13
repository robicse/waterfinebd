<?php

namespace App\Http\Controllers\Common;

use DB;
use App\Helpers\Helper;
use App\Helpers\ErrorTryCatch;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\Category;
use App\Models\Stock;
use App\Models\Product;
use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use DataTables;

class StockController extends Controller
{
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->User = Auth::user();
            if ($this->User->status == 0) {
                $request->session()->flush();
                return redirect('login');
            }
            return $next($request);
        });
        $this->middleware('permission:stocks-list', ['only' => ['index', 'show']]);
    }

    public function index(Request $request)
    {
        try {
            $User=$this->User;
            if ($request->ajax()) {
                $stocks = Stock::with('purchase','product','store')->latest();
                return Datatables::of($stocks)
                    ->addIndexColumn()
                    ->rawColumns(['action', 'status'])
                    ->make(true);
            }

            return view('backend.common.stocks.index');
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function stockLowList(){
        $stores = Store::all();
        return view('backend.common.stocks.stock_low', compact('stores'));
    }

    public function stockLowListDetails($store_id){
        // $purchaseProducts = Purchase::join('stocks','purchases.id','stocks.purchase_id')
        // ->select('stocks.product_id')
        // ->where('purchases.store_id',$store_id)
        // ->groupBy('stocks.product_id')
        // ->get();

        $stock_lows = [];
        $products = Product::wherestatus(1)->get();
        if(count($products) > 0){
            $nested_data = [];
            foreach($products as $product){
                $currentStoreProductCurrentStock = Helper::storeProductCurrentStock($store_id, $product->id);
                if($currentStoreProductCurrentStock < $product->stock_low_qty){
                    $nested_data['store_id'] = $store_id;
                    $nested_data['store_name'] =  Helper::getStoreName($store_id);
                    $nested_data['product_id'] = $product->id;
                    $nested_data['product_name'] = $product->name;
                    $nested_data['stock_low_qty'] = $product->stock_low_qty;
                    $nested_data['current_stock_low_qty'] = $currentStoreProductCurrentStock;
                    array_push($stock_lows, $nested_data);
                }
            }
        }
        $stores = Store::all();
        return view('backend.common.stocks.stock_low_details', compact('stores','stock_lows'));
    }
}
