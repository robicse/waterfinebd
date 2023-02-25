<?php

namespace App\Http\Controllers\Common;

use DB;
use App\Helpers\ErrorTryCatch;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Sale;
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
                    ->addColumn('action', function ($purchase)use($User) {
                        $btn='';
                        if($User->can('sales-edit')){
                        $btn = '<a href=' . route(\Request::segment(1) . '.sales.edit', $purchase->id) . ' class="btn btn-info btn-sm waves-effect"><i class="fa fa-edit"></i></a>';
                        }
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
        $stores = Store::wherestatus(1)->pluck('name','id');
        $customers = Customer::wherestatus(1)->pluck('name','id');
        $categories = Category::wherestatus(1)->get();
        $units = Unit::wherestatus(1)->get();
        $packages = Package::wherestatus(1)->pluck('name','id');
        return view('backend.common.sales.create', compact('stores','customers','categories','units','packages'));
    }

    public function store(Request $request)
    {
        dd($request->all());
        $this->validate($request, [
            // 'name' => 'required|min:1|max:190|unique:sales',
            'category_id' => 'required',
        ]);

        // try {
            $purchase = new Sale();
            $purchase->entry_date = $request->entry_date;
            $purchase->store_id = $request->store_id;
            $purchase->supplier_id = $request->supplier_id;
            $purchase->total_quantity = $request->total_quantity;
            $purchase->total_buy_amount = $request->total_buy_amount;
            $purchase->paid_amount = $request->paid_amount;
            $purchase->discount_amount = $request->discount_amount;
            $purchase->total_sell_amount = $request->total_sell_amount;
            $purchase->status = 1;
            $purchase->created_by_user_id = Auth::User()->id;
            if($purchase->save()){
                for($i=0; $i<count($request->category_id); $i++){
                    $stock = new Stock();
                    $stock->purchase_id = $purchase->id;
                    $stock->store_id = $request->store_id;
                    $stock->category_id = $request->category_id[$i];
                    $stock->product_id = $request->product_id[$i];
                    $stock->quantity = $request->quantity[$i];
                    $stock->buy_price = $request->buy_price[$i];
                    $stock->sell_price = $request->sell_price[$i];
                    $stock->status = 1;
                    $stock->created_by_user_id = Auth::User()->id;
                    $stock->save();
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
        $purchase = Sale::findOrFail($id);
        return view('backend.common.sales.show', compact('purchase'));
    }

    public function edit($id)
    {
        $purchase = Sale::findOrFail($id);
        $packageProducts = Stock::wherepackage_id($id)->get();
        $categories = Category::wherestatus(1)->get();
        $products = Product::wherestatus(1)->get();
        return view('backend.common.sales.edit', compact('purchase','packageProducts','categories','products'));
    }

    public function update(Request $request, $id)
    {
        //dd($request->all());
        $this->validate($request, [
            'name' => "required|min:1|max:190|unique:sales,name,$id",
            'category_id' => 'required',
        ]);

        try {
            $purchase = Sale::findOrFail($id);
            $purchase->name = $request->name;
            $purchase->amount = $request->amount;
            // $purchase->status = $request->status;
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
}
