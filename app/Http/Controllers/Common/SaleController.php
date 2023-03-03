<?php

namespace App\Http\Controllers\Common;

use DB;
use App\Helpers\ErrorTryCatch;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Traits\CurrencyTrait;
use App\Models\PaymentReceipt;
use App\Models\OrderType;
use App\Models\PaymentType;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SalePackage;
use App\Models\Product;
use App\Models\Package;
use App\Models\Store;
use App\Models\Customer;
use App\Models\Stock;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use DataTables;

class SaleController extends Controller
{
    use CurrencyTrait;
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
        $this->middleware('permission:sales-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:sales-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:sales-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:sales-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        try {
            $User=$this->User;
            if ($request->ajax()) {
                $sales = Sale::orderBy('id', 'DESC');
                return Datatables::of($sales)
                    ->addIndexColumn()
                    ->addColumn('store', function ($data) {
                        return $data->store->name;
                    })
                    ->addColumn('customer', function ($data) {
                        return $data->customer->name;
                    })
                    ->addColumn('status', function ($data) {
                        if ($data->status == 0) {
                            return '<div class="form-check form-switch"><input type="checkbox" id="flexSwitchCheckDefault" onchange="updateStatus(this,\'sales\')" class="form-check-input"  value=' . $data->id . ' /></div>';
                        } else {
                            return '<div class="form-check form-switch"><input type="checkbox" id="flexSwitchCheckDefault" checked="" onchange="updateStatus(this,\'sales\')" class="form-check-input"  value=' . $data->id . ' /></div>';
                        }
                    })
                    ->addColumn('action', function ($sale)use($User) {
                        $btn='';
                        // if($User->can('sales-edit')){
                        // $btn = '<a href=' . route(\Request::segment(1) . '.sales.edit', $sale->id) . ' class="btn btn-info btn-sm waves-effect"><i class="fa fa-edit"></i></a>';
                        // }
                        // $btn = '<span  class="d-inline-flex"><a href=' . route(\Request::segment(1) . '.sales.show', $sale->id) . ' class="btn btn-warning btn-sm waves-effect"><i class="fa fa-eye"></i></a>';
                        $btn .= '<a href=' . url(\Request::segment(1) . '/sales-prints/' . $sale->id . '/a4') . ' class="btn btn-info  btn-sm float-left" style="margin-left: 5px"><i class="fa fa-print"></i>A4</a>';
                        // $btn .= '<a href=' . url(\Request::segment(1) . "/sales-prints/" . $sale->id . '/80mm') . ' class="btn btn-info  btn-sm float-left" style="margin-left: 5px"><i class="fa fa-print"></i>80MM</a>';
                        $btn .= '<a target="_blank" href=' . url(\Request::segment(1) . "/sales-invoice-pdf/" . $sale->id) . ' class="btn btn-info  btn-sm float-left" style="margin-left: 5px"><i class="fas fa-file-pdf"></i>PDF</a>';

                        return $btn;
                    })
                    ->rawColumns(['category','action', 'status'])
                    ->make(true);
            }

            return view('backend.common.sales.index');
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
        return view('backend.common.sales.create', compact('stores','customers','categories','units','packages','payment_types','order_types'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            'voucher_date' => 'required',
            'store_id' => 'required',
            'customer_id' => 'required',
            'total_quantity' => 'required',
            'sub_total' => 'required',
            'grand_total' => 'required',
            //'discount' => 'required',
            'paid' => 'required',
            'due' => 'required',
            'product_id.*' => 'required',
            'qty.*' => 'required',
            'sale_price.*' => 'required'
        ]);

        // try {
            $voucher_date = $request->voucher_date;
            $store_id = $request->store_id;
            $customer_id = $request->customer_id;
            $total_quantity = $request->total_quantity;
            $sub_total = $request->sub_total;
            $discount_type = $request->discount_type;
            $discount_percent = $request->discount_percent;
            $discount_amount = $request->discount ? $request->discount : 0;
            $total_vat = $request->total_vat;
            $grand_total = $request->grand_total;
            $after_discount_amount = $grand_total - $discount_amount;
            $paid_amount = $request->paid;
            $due_amount = $request->due;

            $product_id = $request->product_id;
            $unit_id = $request->unit_id;
            $qty = $request->qty;
            $product_vat = $request->product_vat;
            $product_vat_amount = $request->product_vat_amount;
            $sale_price = $request->sale_price;
            $total = $request->total;
            $package_id = $request->package_id;

            $profit_amount = 0;
            for($x=0; $x<count($product_id); $x++){
                $p_id = $product_id[$x];
                $p_qty = $qty[$x];
                $stock_info = Stock::wherestore_id($store_id)->whereproduct_id($p_id)->select('buy_price','sale_price')->orderBy('id', 'DESC')->first();
                if($stock_info){
                    $per_qty_profit_amount = $stock_info->sale_price - $stock_info->buy_price;
                    $profit_amount += $per_qty_profit_amount * $p_qty;
                }
            }

            $sale = new Sale();
            $sale->voucher_date = $voucher_date;
            $sale->store_id = $store_id;
            $sale->customer_id = $customer_id;
            $sale->total_quantity = $total_quantity;
            $sale->sub_total = $sub_total;
            $sale->discount_type = $discount_type;
            $sale->discount_percent = $discount_percent;
            $sale->discount_amount = $discount_amount;
            $sale->total_vat = $total_vat;
            $sale->grand_total = $grand_total;
            $sale->paid_amount = $paid_amount;
            $sale->due_amount = $due_amount;
            $sale->profit_amount = $profit_amount;
            $sale->status = 1;
            $sale->created_by_user_id = Auth::User()->id;
            if($sale->save()){
                for($i=0; $i<count($product_id); $i++){
                    $product = Product::whereid($product_id[$i])->first();
                    $p_id = $product_id[$i];
                    $p_qty = $qty[$i];
                    $total_amount = 0;
                    $stock_info = Stock::wherestore_id($store_id)->whereproduct_id($p_id)->select('buy_price','sale_price')->orderBy('id', 'DESC')->first();
                    if($stock_info){
                        $per_qty_profit_amount = $stock_info->sale_price - $stock_info->buy_price;
                        $total_amount += $per_qty_profit_amount * $p_qty;
                    }

                    $sub_total = ($qty[$i] * $sale_price[$i]);
                    $unit_id = $unit_id[$i];
                    $product_vat = $product_vat[$i] != NULL ? $request->product_vat : 0;
                    $product_vat_amount = $product_vat_amount[$i];
                    $producttotal = $total[$i];
                    $final_discount_amount = 0;
                    $extra_discount_amount = NULL;
                    if ($discount_type != NULL) {
                        //$discount_amount = $request->discount;

                        // including vat
                        $cal_discount = $discount_amount;
                        $cal_product_total_amount = $product_vat_amount + $producttotal;
                        $cal_grand_total = $sub_total + $total_vat;

                        $cal_discount_amount =  (round((float)$cal_discount, 2) * round((float)$cal_product_total_amount, 2)) / round((float)$cal_grand_total, 2);
                        $final_discount_amount = round((float)$cal_discount_amount, 2);
                        $per_product_discount =  $final_discount_amount / $qty[$i];
                    }

                    $sale_product = new SaleProduct();
                    $sale_product->sale_id = $sale->id;
                    $sale_product->store_id = $store_id;
                    $sale_product->category_id =$product->category_id;
                    $sale_product->unit_id = $product->unit_id;
                    $sale_product->product_id = $product_id[$i];
                    $sale_product->qty = $qty[$i];
                    $sale_product->sale_price = $sale_price[$i];
                    $sale_product->total = $sub_total;
                    //$sale_detail->date = $date;
                    $sale_product->product_vat = $product_vat;
                    $sale_product->product_vat_amount = $product_vat_amount;
                    $sale_product->product_discount_type = $discount_type;
                    $sale_product->per_product_discount = $per_product_discount;
                    $sale_product->product_discount_percent = $discount_percent;
                    $sale_product->product_discount = $final_discount_amount;
                    $sale_product->after_product_discount = ($sub_total + $product_vat_amount) - $final_discount_amount;
                    $sale_product->product_total = ($sub_total + $product_vat_amount) - $final_discount_amount;
                    $sale_product->total_profit = $total_amount;
                    $sale_product->created_by_user_id = Auth::User()->id;
                    $sale_product->save();
                }
                if($package_id){
                    $sale_package = new SalePackage();
                    $sale_package->sale_id = $sale->id;
                    $sale_package->store_id = $store_id;
                    $sale_package->package_id = $package_id;
                    $sale_package->amount = Package::whereid($package_id)->pluck('amount')->first();
                    $sale_package->created_by_user_id = Auth::User()->id;
                    $sale_package->save();
                }

                // for due amount > 0
                if($due_amount > 0){
                    $payment_receipt = new PaymentReceipt();
                    $payment_receipt->date = date('Y-m-d');
                    $payment_receipt->store_id = $store_id;
                    $payment_receipt->order_type = 'Sale';
                    $payment_receipt->order_id = $sale->id;
                    $payment_receipt->customer_id = $customer_id;
                    $payment_receipt->order_type_id = 2;
                    $payment_receipt->amount = $due_amount;
                    $payment_receipt->created_by_user_id = Auth::User()->id;
                    $payment_receipt->save();
                }
                // for paid amount > 0
                if($paid_amount > 0){
                    $payment_receipt = new PaymentReceipt();
                    $payment_receipt->date = date('Y-m-d');
                    $payment_receipt->store_id = $store_id;
                    $payment_receipt->order_type = 'Sale';
                    $payment_receipt->order_id = $sale->id;
                    $payment_receipt->customer_id = $customer_id;
                    $payment_receipt->order_type_id = 1;
                    $payment_receipt->payment_type_id = 1;
                    $payment_receipt->amount = $paid_amount;
                    $payment_receipt->created_by_user_id = Auth::User()->id;
                    $payment_receipt->save();
                }
            }

            Toastr::success("Sale Created Successfully", "Success");
            return redirect()->route(\Request::segment(1) . '.sales.index');
        // } catch (\Exception $e) {
        //     $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
        //     Toastr::error($response['message'], "Error");
        //     return back();
        // }
    }

    public function show($id)
    {
        $sale = Sale::findOrFail($id);
        return view('backend.common.sales.show', compact('sale'));
    }

    public function edit($id)
    {
        $sale = Sale::findOrFail($id);
        $packageProducts = Stock::wherepackage_id($id)->get();
        $categories = Category::wherestatus(1)->get();
        $products = Product::wherestatus(1)->get();
        return view('backend.common.sales.edit', compact('sale','packageProducts','categories','products'));
    }

    public function update(Request $request, $id)
    {
        //dd($request->all());
        $this->validate($request, [
            'voucher_date' => 'required',
            'store_id' => 'required',
            'customer_id' => 'required',
            'total_quantity' => 'required',
            'payable_amount' => 'required',
            'grand_total' => 'required',
            'discount_amount' => 'required',
            'paid_amount' => 'required',
            'product_category_id.*' => 'required',
            'product_id.*' => 'required',
            'quantity.*' => 'required',
            'amount.*' => 'required'
        ]);

        try {
            $sale = Sale::findOrFail($id);
            $sale->name = $request->name;
            $sale->amount = $request->amount;
            // $sale->status = $request->status;
            $sale->updated_by_user_id = Auth::User()->id;
            if($sale->save()){
                DB::table('package_products')->wherepackage_id($id)->delete();
                for($i=0; $i<count($request->category_id); $i++){
                    $sale_product = new Stock();
                    $sale_product->package_id = $id;
                    $sale_product->product_id = $request->product_id[$i];
                    $sale_product->quantity = $request->quantity[$i];
                    $sale_product->created_by_user_id = Auth::User()->id;
                    $sale_product->updated_by_user_id = Auth::User()->id;
                    $sale_product->save();
                }
            }
            Toastr::success("Sale Updated Successfully", "Success");
            return redirect()->route(\Request::segment(1) . '.sales.index');
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

    public function salePrintWithPageSize($id, $pagesize)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $sale = Sale::findOrFail($id);
        $saleProducts = SaleProduct::where('sale_id', $id)->get();
        $previousDue= Sale::where('id','!=',$id)->wherecustomer_id($sale->customer_id)->sum('due_amount');
        return view('backend.common.sales.print_with_size', compact('sale', 'saleProducts', 'pagesize','previousDue','default_currency'));
    }

    public function saleInvoicePdfDownload($id)
    {
        $sale = Sale::findOrFail($id);
        $saleProducts = SaleProduct::where('sale_id', $id)->get();
        $previousDue= Sale::where('id','!=',$id)->wherecustomer_id($sale->customer_id)->sum('due_amount');
        $pdf = Pdf::loadView('backend.common.sales.invoice_pdf', compact('sale', 'saleProducts','previousDue'));
        return $pdf->download('saleinvoice_' . now() . '.pdf');
    }
}
