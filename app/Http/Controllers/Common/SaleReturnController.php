<?php

namespace App\Http\Controllers\Common;

use DB;
use App\Helpers\ErrorTryCatch;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SaleReturn;
use App\Models\Product;
use App\Models\Store;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\Customer;
use App\Models\SaleReturnDetail;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use DataTables;

class SaleReturnController extends Controller
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
        // $this->middleware('permission:sale-returns-list', ['only' => ['index', 'show']]);
        // $this->middleware('permission:sale-returns-create', ['only' => ['create', 'store']]);
        // $this->middleware('permission:sale-returns-edit', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:sale-returns-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        try {
            $User=$this->User;
            if ($request->ajax()) {
                $saleReturns = SaleReturn::orderBy('id', 'DESC');
                return Datatables::of($saleReturns)
                    ->addIndexColumn()
                    ->addColumn('store', function ($data) {
                        return $data->store->name;
                    })
                    ->addColumn('customer', function ($data) {
                        return $data->customer->name;
                    })
                    ->addColumn('status', function ($data) {
                        if ($data->status == 0) {
                            return '<div class="form-check form-switch"><input type="checkbox" id="flexSwitchCheckDefault" onchange="updateStatus(this,\'sale-returns\')" class="form-check-input"  value=' . $data->id . ' /></div>';
                        } else {
                            return '<div class="form-check form-switch"><input type="checkbox" id="flexSwitchCheckDefault" checked="" onchange="updateStatus(this,\'sale-returns\')" class="form-check-input"  value=' . $data->id . ' /></div>';
                        }
                    })
                    ->addColumn('action', function ($sale_return)use($User) {
                        $btn='';
                        $btn .= '<span  class="d-inline-flex"><a href=' . route(\Request::segment(1) . '.sale-returns.show', $sale_return->id) . ' class="btn btn-warning btn-sm waves-effect"><i class="fa fa-eye"></i></a>';
                        if($User->can('sale-returns-edit')){
                        $btn = '<a href=' . route(\Request::segment(1) . '.sale-returns.edit', $sale_return->id) . ' class="btn btn-info btn-sm waves-effect"><i class="fa fa-edit"></i></a></span>';
                        }
                        return $btn;
                    })
                    ->rawColumns(['category','action', 'status'])
                    ->make(true);
            }

            return view('backend.common.sale_returns.index');
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function create()
    {
        $stores = Store::wherestatus(1)->pluck('name','id');
        $sales = Sale::wherestatus(1)->pluck('id','id');
        $customers = Customer::wherestatus(1)->pluck('name','id');
        $categories = Category::wherestatus(1)->get();
        return view('backend.common.sale_returns.create', compact('stores','sales','customers','categories'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            'sale_id' => 'required',
            'return_date' => 'required',
            //'store_id' => 'required',
            //'customer_id' => 'required',
            'total_quantity' => 'required',
            'product_category_id.*' => 'required',
            'product_id.*' => 'required',
            'qty.*' => 'required'
        ]);

        // try {
            $sale = Sale::findOrFail($request->sale_id);
            $sale_return = new SaleReturn();
            $sale_return->return_date = $request->return_date;
            $sale_return->sale_id = $sale->id;
            $sale_return->store_id = $sale->store_id;
            $sale_return->customer_id = $sale->customer_id;
            $sale_return->total_quantity = $request->total_quantity;
            $sale_return->comments = $request->comments;
            $sale_return->status = 1;
            $sale_return->created_by_user_id = Auth::User()->id;
            if($sale_return->save()){
                $profit_minus_amount = 0;
                for($i=0; $i<count($request->category_id); $i++){
                    $saleProduct = SaleProduct::wheresale_id($sale->id)->whereproduct_id($request->product_id[$i])->first();
                    $profit_minus_amount += $saleProduct->per_product_profit * $request->qty[$i];
                    $sale_return_detail = new SaleReturnDetail();
                    $sale_return_detail->sale_return_id = $sale_return->id;
                    $sale_return_detail->store_id = $sale->store_id;
                    $sale_return_detail->category_id = $request->category_id[$i];
                    $sale_return_detail->product_id = $request->product_id[$i];
                    $sale_return_detail->qty = $request->qty[$i];
                    $sale_return_detail->amount = $saleProduct->sale_price;
                    $sale_return_detail->profit_minus = $saleProduct->total_profit;
                    $sale_return_detail->created_by_user_id = Auth::User()->id;
                    $sale_return_detail->save();

                    $previous_already_return_qty = $saleProduct->already_return_qty;
                    $saleProduct->already_return_qty = ($previous_already_return_qty + $request->qty[$i]);
                    $saleProduct->save();
                }

                $sale_return->receivable_amount = $saleProduct->sale_price;
                $sale_return->receive_amount = $saleProduct->sale_price;
                $sale_return->profit_minus_amount = $profit_minus_amount;
                $sale_return->update();
            }

            Toastr::success("SaleReturn Created Successfully", "Success");
            return redirect()->route(\Request::segment(1) . '.sale-returns.index');
        // } catch (\Exception $e) {
        //     $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
        //     Toastr::error($response['message'], "Error");
        //     return back();
        // }
    }

    public function show($id)
    {

        $SaleReturn = SaleReturn::findOrFail($id);
        $store= Store::findOrFail($SaleReturn->store_id);
        $SaleReturnDetails = SaleReturnDetail::wheresale_return_id($SaleReturn->id)->get();
        return view('backend.common.sale_returns.show', compact('store','SaleReturn','SaleReturnDetails'));
    }

    public function edit($id)
    {
        $sale_return = SaleReturn::findOrFail($id);
        $packageProducts = Stock::wherepackage_id($id)->get();
        $categories = Category::wherestatus(1)->get();
        $products = Product::wherestatus(1)->get();
        return view('backend.common.sale_returns.edit', compact('sale','packageProducts','categories','products'));
    }

    public function update(Request $request, $id)
    {
        //dd($request->all());
        $this->validate($request, [
            'return_date' => 'required',
            'store_id' => 'required',
            'customer_id' => 'required',
            'total_quantity' => 'required',
            'product_category_id.*' => 'required',
            'product_id.*' => 'required',
            'qty.*' => 'required'
        ]);

        try {
            $sale_return = SaleReturn::findOrFail($id);
            $sale_return->name = $request->name;
            $sale_return->amount = $request->amount;
            $sale_return->updated_by_user_id = Auth::User()->id;
            if($sale_return->save()){
                DB::table('package_products')->wherepackage_id($id)->delete();
                for($i=0; $i<count($request->category_id); $i++){
                    $sale_return_detail = new Stock();
                    $sale_return_detail->package_id = $id;
                    $sale_return_detail->product_id = $request->product_id[$i];
                    $sale_return_detail->qty = $request->qty[$i];
                    $sale_return_detail->amount = $request->qty[$i];
                    $sale_return_detail->profit_minus = $request->qty[$i];
                    $sale_return_detail->created_by_user_id = Auth::User()->id;
                    $sale_return_detail->updated_by_user_id = Auth::User()->id;
                    $sale_return_detail->save();
                }
            }
            Toastr::success("SaleReturn Updated Successfully", "Success");
            return redirect()->route(\Request::segment(1) . '.sale-returns.index');
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function destroy($id)
    {
        //
    }

    public function FindProductBySearchProductName(Request $request)
    {
        if ($request->has('q')) {
            $data = Product::where('status', 1)
                ->where('name', 'like', '%' . $request->q . '%')
                ->select('id', 'name')->get();
            if ($data) {
                return response()->json($data);
            } else {
                return response()->json(['success' => false, 'product' => 'Error!!']);
            }
        }
    }

    public function categoryProductInfo(Request $request)
    {
        $options = [
            'productOptions' => '',
        ];
        $category_id = $request->current_category_id;
        $products = Product::wherestatus(1)->wherecategory_id($category_id)->get();
        if (count($products) > 0) {
            $options['productOptions'] .= "<option value=''>Select Product</option>";
            foreach ($products as $key => $product) {
                $options['productOptions'] .= "<option value='$product->id'>" . $product->name . "</option>";
            }
        } else {
            $options['productOptions'] .= "<option value=''>No Data Found!</option>";
        }

        return response()->json(['success' => true, 'data' => $options]);
    }
}
