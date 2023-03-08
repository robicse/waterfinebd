<?php

namespace App\Http\Controllers\Common;

use DB;
use App\Helpers\ErrorTryCatch;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderType;
use App\Models\PaymentType;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Stock;
use App\Models\PaymentReceipt;
use App\Http\Traits\CurrencyTrait;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use DataTables;

class PurchaseController extends Controller
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
        $this->middleware('permission:purchases-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:purchases-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:purchases-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:purchases-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        try {
            $User=$this->User;
            if ($request->ajax()) {
                $purchases = Purchase::orderBy('id', 'DESC');
                return Datatables::of($purchases)
                    ->addIndexColumn()
                    ->addColumn('store', function ($data) {
                        return $data->store->name;
                    })
                    ->addColumn('supplier', function ($data) {
                        return $data->supplier->name;
                    })
                    ->addColumn('status', function ($data) {
                        if ($data->status == 0) {
                            return '<div class="form-check form-switch"><input type="checkbox" id="flexSwitchCheckDefault" onchange="updateStatus(this,\'purchases\')" class="form-check-input"  value=' . $data->id . ' /></div>';
                        } else {
                            return '<div class="form-check form-switch"><input type="checkbox" id="flexSwitchCheckDefault" checked="" onchange="updateStatus(this,\'purchases\')" class="form-check-input"  value=' . $data->id . ' /></div>';
                        }
                    })
                    ->addColumn('action', function ($purchase)use($User) {
                        $btn='';
                        $btn .= '<span  class="d-inline-flex"><a href=' . route(\Request::segment(1) . '.purchases.show', $purchase->id) . ' class="btn btn-sm btn-warning waves-effect float-left" style="margin-left: 5px"><i class="fa fa-eye"></i></a>';
                        // if($User->can('purchases-edit')){
                        // $btn .= '<a href=' . route(\Request::segment(1) . '.purchases.edit', $purchase->id) . ' class="btn btn-info btn-sm waves-effect"><i class="fa fa-edit"></i></a></span>';
                        // }
                        return $btn;
                    })
                    ->rawColumns(['category','action', 'status'])
                    ->make(true);
            }

            return view('backend.common.purchases.index');
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
        $suppliers = Supplier::wherestatus(1)->pluck('name','id');
        $categories = Category::wherestatus(1)->get();
        $units = Unit::wherestatus(1)->get();
        return view('backend.common.purchases.create', compact('stores','suppliers','categories','order_types','payment_types','units'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $this->validate($request, [
            'entry_date' => 'required',
            'store_id' => 'required',
            'supplier_id' => 'required',
            'total_qty' => 'required',
            'sub_total' => 'required',
            'grand_total' => 'required',
            'total_sale_price' => 'required',
            //'discount_amount' => 'required',
            'paid' => 'required',
            'product_category_id.*' => 'required',
            'product_id.*' => 'required',
            'qty.*' => 'required',
            'purchase_price.*' => 'required',
            'sale_price.*' => 'required'
        ]);

        try {
            $entry_date = $request->entry_date;
            $store_id = $request->store_id;
            $supplier_id = $request->supplier_id;
            $total_qty = $request->total_qty;
            $sub_total = $request->sub_total;
            $discount_amount = $request->discount ? $request->discount : 0;
            $grand_total = $request->grand_total;
            $total_sale_price = $request->total_sale_price;
            $payment_type_id = $request->payment_type_id;
            $paid_amount = $request->paid;
            $due_amount = $request->due;
            $discount_type = 'Flat';
            $discount_percent = NULL;
            $discount = 0;
            $total_vat = $request->total_vat;
            //$category_id = $request->category_id;
            $unit_id = $request->unit_id;
            $product_id = $request->product_id;
            $qty = $request->qty;
            $purchase_price = $request->purchase_price;
            $sale_price = $request->sale_price;

            $purchase = new Purchase();
            $purchase->entry_date = $entry_date;
            $purchase->store_id = $store_id;
            $purchase->supplier_id = $supplier_id;
            $purchase->total_qty = $total_qty;
            $purchase->sub_total = $sub_total;
            $purchase->discount_amount = $discount_amount;
            $purchase->total_vat = $total_vat;
            $purchase->discount_type = $discount_type;
            $purchase->discount_percent = $discount_percent;
            $purchase->after_discount = $grand_total;
            $purchase->grand_total = $grand_total;
            $purchase->payment_type_id = $payment_type_id;
            $purchase->paid_amount = $paid_amount;
            $purchase->due_amount = $due_amount;
            $purchase->total_sale_price = $total_sale_price;
            $purchase->status = 1;
            $purchase->created_by_user_id = Auth::User()->id;
            if($purchase->save()){
                for($i=0; $i<count($product_id); $i++){
                    $product = Product::whereid($product_id[$i])->first();
                    $p_id = $product_id[$i];
                    $p_price = $purchase_price[$i];
                    $p_sale_price = $sale_price[$i];
                    $p_qty = $qty[$i];
                    $total = ($p_qty * $p_price);
                    //change  to unit id
                    $unit_id = $request->unit_id[$i];
                    $product_vat = $request->product_vat[$i];
                    $product_vat_amount = $request->product_vat_amount[$i];
                    $average_purchase_price = 0;
                    //discount calculation
                    $final_discount_amount = 0;

                    $stock = new Stock();
                    $stock->purchase_id = $purchase->id;
                    $stock->store_id = $store_id;
                    //$stock->category_id = $product->category_id;
                    $stock->qty = $p_qty;
                    $stock->product_id = $p_id;
                    $stock->already_return_qty = 0;
                    $stock->purchase_price = $p_price;
                    $stock->sale_price = $p_sale_price;
                    $stock->product_total = $total;
                    $stock->product_vat = $product_vat;
                    $stock->product_vat_amount = $product_vat_amount;
                    $stock->product_total = $total - $final_discount_amount + $product_vat_amount;
                    $stock->product_discount_type = $discount_type;
                    $stock->product_discount_percent = $discount_percent;
                    $stock->product_discount = $final_discount_amount;
                    $stock->after_product_discount = $total - $final_discount_amount + $product_vat_amount;
                    $stock->created_by_user_id = Auth::User()->id;
                    $stock->save();
                }

                // for due amount > 0
                if($due_amount > 0){
                    $payment_receipt = new PaymentReceipt();
                    $payment_receipt->date = date('Y-m-d');
                    $payment_receipt->store_id = $store_id;
                    $payment_receipt->order_type = 'Purchase';
                    $payment_receipt->order_id = $purchase->id;
                    $payment_receipt->supplier_id = $supplier_id;
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
                    $payment_receipt->order_type = 'Purchase';
                    $payment_receipt->order_id = $purchase->id;
                    $payment_receipt->supplier_id = $supplier_id;
                    $payment_receipt->order_type_id = 1;
                    $payment_receipt->payment_type_id = $request->payment_type_id;
                    $payment_receipt->bank_name = $request->bank_name ? $request->bank_name : '';
                    $payment_receipt->cheque_number = $request->cheque_number ? $request->cheque_number : '';
                    $payment_receipt->cheque_date = $request->cheque_date ? $request->cheque_date : '';
                    $payment_receipt->transaction_number = $request->transaction_number ? $request->transaction_number : '';
                    $payment_receipt->note = $request->note ? $request->note : '';
                    $payment_receipt->amount = $paid_amount;
                    $payment_receipt->created_by_user_id = Auth::User()->id;
                    $payment_receipt->save();
                }
            }

            Toastr::success("Purchase Created Successfully", "Success");
            return redirect()->route(\Request::segment(1) . '.purchases.index');
        } catch (\Exception $e) {
            $response = ErrorTryCatch::createResponse(false, 500, 'Internal Server Error.', null);
            Toastr::error($response['message'], "Error");
            return back();
        }
    }

    public function show($id)
    {
        $default_currency = $this->getCurrencyInfoByDefaultCurrency();
        $purchase = Purchase::findOrFail($id);
        $supplier = Supplier::findOrFail($purchase->supplier_id);
        $purchaseDetails = Stock::where('purchase_id', $purchase->id)->get();
        return view('backend.common.purchases.show', compact('purchase', 'supplier','purchaseDetails', 'default_currency'));
    }

    public function edit($id)
    {
        $purchase = Purchase::findOrFail($id);
        $packageProducts = Stock::wherepackage_id($id)->get();
        $categories = Category::wherestatus(1)->get();
        $products = Product::wherestatus(1)->get();
        return view('backend.common.purchases.edit', compact('purchase','packageProducts','categories','products'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'entry_date' => 'required',
            'store_id' => 'required',
            'supplier_id' => 'required',
            'total_qty' => 'required',
            'total_sale_price' => 'required',
            'grand_total' => 'required',
            'discount_amount' => 'required',
            'paid_amount' => 'required',
            'product_category_id.*' => 'required',
            'product_id.*' => 'required',
            'quantity.*' => 'required',
            'purchase_price.*' => 'required',
            'sale_price.*' => 'required'
        ]);

        try {
            $purchase = Purchase::findOrFail($id);
            $purchase->name = $request->name;
            $purchase->amount = $request->amount;
            $purchase->updated_by_user_id = Auth::User()->id;
            if($purchase->save()){
                DB::table('package_products')->wherepackage_id($id)->delete();
                for($i=0; $i<count($request->category_id); $i++){
                    $stock = new Stock();
                    $stock->package_id = $id;
                    $stock->product_id = $request->product_id[$i];
                    $stock->quantity = $request->quantity[$i];
                    $stock->created_by_user_id = Auth::User()->id;
                    $stock->updated_by_user_id = Auth::User()->id;
                    $stock->save();
                }
            }
            Toastr::success("Purchase Updated Successfully", "Success");
            return redirect()->route(\Request::segment(1) . '.purchases.index');
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
