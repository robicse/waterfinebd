<?php

namespace App\Http\Controllers\Common;

use DB;
use App\Helpers\ErrorTryCatch;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentReceipt;
use App\Models\OrderType;
use App\Models\PaymentType;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Product;
use App\Models\Store;
use App\Models\Package;
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
                        $btn .= '<a href=' . route(\Request::segment(1) . '.sale-returns.edit', $sale_return->id) . ' class="btn btn-info btn-sm waves-effect float-left" style="margin-left: 5px"><i class="fa fa-edit"></i></a>';
                        }
                        $btn .= '<form method="post" action=' . route(\Request::segment(1) . '.sale-returns.destroy',$sale_return->id) . '">'.csrf_field().'<input type="hidden" name="_method" value="DELETE">';
                        $btn .= '<button class="btn btn-sm btn-danger" style="margin-left: 5px;" type="submit" onclick="return confirm(\'You Are Sure This Delete !\')"><i class="fa fa-trash"></i></button>';
                        $btn .= '</form></span>';

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
        $order_types = OrderType::whereIn('name', ['Cash', 'Credit'])->get();
        $payment_types = PaymentType::whereIn('name', ['Cash', 'Card', 'Cheque', 'Condition'])->get();
        $stores = Store::wherestatus(1)->pluck('name','id');
        $customers = Customer::wherestatus(1)->pluck('name','id');
        $categories = Category::wherestatus(1)->get();
        $units = Unit::wherestatus(1)->get();
        $packages = Package::wherestatus(1)->pluck('name','id');

        $sales = Sale::wherestatus(1)->pluck('id','id');
        return view('backend.common.sale_returns.create', compact('stores','customers','categories','units','packages','payment_types','order_types','sales'));
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

        try {
            DB::beginTransaction();
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
                for($i=0; $i<count($request->product_id); $i++){
                    $saleProduct = SaleProduct::wheresale_id($sale->id)->whereproduct_id($request->product_id[$i])->first();
                    $profit_minus_amount += $saleProduct->per_product_profit * $request->qty[$i];
                    $sale_return_detail = new SaleReturnDetail();
                    $sale_return_detail->sale_return_id = $sale_return->id;
                    $sale_return_detail->store_id = $sale->store_id;
                    // $sale_return_detail->category_id = $request->category_id[$i];
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

                // for paid amount > 0
                // if($paid_amount > 0){
                    $payment_receipt = new PaymentReceipt();
                    $payment_receipt->date = date('Y-m-d');
                    $payment_receipt->store_id = $sale->store_id;
                    $payment_receipt->order_type = 'Sale Return';
                    $payment_receipt->order_id = $sale->id;
                    $payment_receipt->customer_id = $sale->customer_id;
                    $payment_receipt->order_type_id = 1;
                    $payment_receipt->payment_type_id = 1;
                    $payment_receipt->bank_name = $request->bank_name ? $request->bank_name : '';
                    $payment_receipt->cheque_number = $request->cheque_number ? $request->cheque_number : '';
                    $payment_receipt->cheque_date = $request->cheque_date ? $request->cheque_date : '';
                    $payment_receipt->transaction_number = $request->transaction_number ? $request->transaction_number : '';
                    $payment_receipt->note = $request->note ? $request->note : '';
                    $payment_receipt->amount = $saleProduct->sale_price;
                    $payment_receipt->created_by_user_id = Auth::User()->id;
                    $payment_receipt->save();
                // }
            }
            DB::commit();
            Toastr::success("SaleReturn Created Successfully", "Success");
            return redirect()->route(\Request::segment(1) . '.sale-returns.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
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
        $sale = SaleReturn::with('customer')->findOrFail($id);
        $saleDetails = SaleDetails::where('sale_id', $id)->get();

        $payment_types = PaymentType::where('name', '!=', 'LC')->get();
        return view('backend.common.sale_returns.edit', compact('sale', 'saleDetails', 'vans', 'salesmans', 'payment_types'));
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
            DB::beginTransaction();
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
            DB::commit();
            Toastr::success("SaleReturn Updated Successfully", "Success");
            return redirect()->route(\Request::segment(1) . '.sale-returns.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $saleReturn = SaleReturn::find($id);
            $saleReturnDetails= DB::table('sale_return_details')->where('sale_return_id',$id)->get();
            $countSaleReturnDetails = count($saleReturnDetails);
            if($countSaleReturnDetails > 0){
                foreach($saleReturnDetails as $saleReturnDetail){
                    $saleProduct = SaleProduct::wheresale_id($saleReturn->sale_id)->whereproduct_id($saleReturnDetail->product_id)->first();
                    if($saleProduct){
                        $exists_qty = $saleProduct->already_return_qty;
                        $saleProduct->already_return_qty = $exists_qty - $saleReturnDetail->qty;
                        $saleProduct->save();
                    }
                }
            }
            DB::table('sale_return_details')->where('sale_return_id',$id)->delete();
            DB::table('payment_receipts')->where('order_id',$id)->whereorder_type('Sale Return')->delete();
            $saleReturn->delete();
            DB::commit();
            Toastr::success("SaleReturn Created Successfully", "Success");
            return redirect()->route(\Request::segment(1) . '.sale-returns.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
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

    public function saleInfo(Request $request)
    {
        $sale = Sale::findOrFail($request->sale_id);
        $options = [
            'storeOptions' => '',
            'customerOptions' => '',
        ];
        $store = Store::findOrFail($sale->store_id);
        if($store){
            $options['storeOptions'] .= "<option value='$store->id'>" . $store->name . "</option>";
        }
        $customer = Customer::findOrFail($sale->customer_id);
        if($customer){
            $options['customerOptions'] .= "<option value='$customer->id'>" . $customer->name . "</option>";
        }
        return response()->json(['success' => true, 'data' => $options]);
    }
}
